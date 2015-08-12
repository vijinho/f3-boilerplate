<?php

namespace FFMVC\Controllers\API;

use \FFMVC\Helpers as Helpers;

/**
 * Api Controller Class.
 *
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Api
{
    /**
     * version.
     *
     * @var version
     */
    protected $version = 1;

    /**
     * response errors
     * 1xx: Informational - Transfer Protocol Information
     * 2xx: Success - Client's request successfully accepted
     *     - 200 OK, 201 - Created, 202 - Accepted, 204 - No Content (purposefully)
     * 3xx: Redirection - Client needs additional action to complete request
     *     - 301 - new location for resource
     *     - 304 - not modified
     * 4xx: Client Error - Client caused the problem
     *     - 400 - Bad request - nonspecific failure
     *     - 401 - unauthorised
     *     - 403 - forbidden
     *     - 404 - not found
     *     - 405 - method not allowed
     *     - 406 - not acceptable (e.g. not in correct format like json)
     * 5xx: Server Error - The server was responsible.
     *
     * @var errors
     */
    protected $errors = array();

    /**
     * response data.
     *
     * @var data
     */
    protected $data = array();

    /**
     * response params.
     *
     * @var params
     */
    protected $params = array();

    /**
     * response helper.
     *
     * @var response
     */
    protected $response;

    /**
     * db.
     *
     * @var db
     */
    protected $db;


    /**
     * Error format required by RFC6794
     *
     * @var type
     * @url https://tools.ietf.org/html/rfc6749
     */
    protected $OAuthErrorTypes = array(
        'invalid_request' => array(
            'code' => 'invalid_request',
            'description' => 'The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.',
            'uri' => '',
            'state' => ''
        ),
        'invalid_client' => array(
            'code' => 'invalid_client',
            'description' => 'Client authentication failed (e.g., unknown client, no client authentication included, or unsupported authentication method).',
            'uri' => '',
            'state' => ''
        ),
        'invalid_grant' => array(
            'code' => 'invalid_grant',
            'description' => 'The provided authorization grant (e.g., authorization code, resource owner credentials) or refresh token is invalid, expired, revoked, does not match the redirection URI used in the authorization request, or was issued to another client.',
            'uri' => '',
            'state' => ''
        ),
        'unsupported_grant_type' => array(
            'code' => 'unsupported_grant_type',
            'description' => 'The authorization grant type is not supported by the authorization server.',
            'uri' => '',
            'state' => ''
        ),
        'unauthorized_client' => array(
            'code' => 'unauthorized_client',
            'description' => 'The client is not authorized to request an authorization code using this method.',
            'uri' => '',
            'state' => ''
        ),
        'access_denied' => array(
            'code' => 'access_denied',
            'description' => 'The resource owner or authorization server denied the request.',
            'uri' => '',
            'state' => ''
        ),
        'unsupported_response_type' => array(
            'code' => 'unsupported_response_type',
            'description' => 'The authorization server does not support obtaining an authorization code using this method.',
            'uri' => '',
            'state' => ''
        ),
        'invalid_scope' => array(
            'code' => 'invalid_scope',
            'description' => 'The requested scope is invalid, unknown, or malformed.',
            'uri' => '',
            'state' => ''
        ),
        'server_error' => array(
            'code' => 'server_error',
            'description' => 'The authorization server encountered an unexpected condition that prevented it from fulfilling the request.',
            'uri' => '',
            'state' => ''
        ),
        'temporarily_unavailable' => array(
            'code' => 'temporarily_unavailable',
            'description' => 'The authorization server is currently unable to handle the request due to a temporary overloading or maintenance of the server.',
            'uri' => '',
            'state' => ''
        )
    );

    /**
     * The OAuth Error to return if an OAuthError occurs
     *
     * @var array OAuthError
     */
    protected $OAuthError = null;

    /**
     * initialize.
     */
    public function __construct()
    {
        $f3 = \Base::instance();
        $this->db = \Registry::get('db');
        $this->response = Helpers\Response::instance();
        $params = $f3->get('PARAMS');

        // get the access token and set it in REQUEST.access_token
        foreach ($f3->get('SERVER') as $k => $header) {
            if (stristr($k, 'authorization') !== false) {
                if (preg_match('/Bearer\s+(?P<access_token>.+)$/i', $header, $matches)) {
                    $token = $matches['access_token'];
                    break;
                }
            }
        }
        if (empty($token)) {
            $token = $f3->get('REQUEST.access_token');
        }
        if (!empty($token)) {
            $f3->set('REQUEST.access_token', $token);
        }
    }

    /**
     * authorize all incoming requests.
     */
    public function beforeRoute($f3, $params)
    {
        // api call does not need authorization
        if ($params[0] != '/api') {
            if (!$this->authorize($f3, $params)) {
            }
        }
    }

    /**
     * compile and send the json response.
     */
    public function afterRoute($f3, $params)
    {
        $version = (int) $f3->get('GET.version');
        if (empty($version)) {
            $version = $this->version;
        }
        if ($version !== $this->version) {
            $this->failure(4999, 'Unknown API version requested.', 400);
        }
        if (empty($this->data['href'])) {
            $data['href'] = $this->href();
        }
        $data = array(
            'service' => 'API',
            'api' => $version,
            'time' => time(),
//            'protocol' => $f3->get('SCHEME'),
        ) + $this->data;

        // if an OAuthError is set, return that instead of errors array
        if (!empty($this->OAuthError)) {
            $data['error'] = $this->OAuthError;
        } else {
            if (count($this->errors)) {
                ksort($this->errors);
                $data['errors'] = $this->errors;
            }
        }
        $return = $f3->get('GET.return');
        switch ($return) {
            case 'xml':
            $this->response->xml($data, $this->params);
            break;

            default:
            case 'json':
            $this->response->json($data, $this->params);
        }
    }

    /**
     * add to the list of errors that occured during this request.
     *
     * @param int    $code        the error code
     * @param string $message     the error message
     * @param int    $http_status the http status code
     */
    public function failure($code, $message, $http_status = null)
    {
        $this->errors[$code] = $message;
        if (!empty($http_status)) {
            $this->params['http_status'] = $http_status;
        }
    }

    /**
     * Get OAuth Error Type
     * @param type $type
     * @return mixed array error type or boolean false
     */
    final protected function getOAuthErrorType($type)
    {
        return array_key_exists($type, $this->OAuthErrorTypes) ? $this->OAuthErrorTypes[$type] : false;
    }

    /**
     * Set the RFC-compliant OAuth Error to return
     *
     * @param type $code of error code from RFC
     * @param type $state optional application state
     * @throws ApiException
     * @return the OAuth error array
     */
    public function setOAuthError($code, $state = null)
    {
        $error = $this->getOAuthErrorType($code);
        if (empty($error)) {
            throw new ApiException('Invalid OAuth error type.', 5100);
        } else {
            if (empty($state)) {
                unset($error['state']);
            } else {
                $error['state'] = $state;
            }
            switch ($code) {
                case 'invalid_client': // as per-spec
                    $this->params['http_status'] = 401;
                    break;
                default:
                    $this->params['http_status'] = 400;
                    break;
            }
            $this->OAuthError = $error;
        }
        return $error;
    }

    /**
     * authenticate and check account_group of user.
     *
     * @return bool true/false if authenticated successfully
     */
    public function authorize($f3, $params)
    {
        try {
        } catch (\Exception $e) {
            // set failure message for user
            switch ($e->getCode()) {

            }

            return false;
        }

        return true;
    }

    /**
     * Basic Authentication for login:password of developer
     * Check that the credentials match the database
     * Cache result for 60 seconds
     * @return boolean success/failure
     */
    protected function basicAuthenticate($f3, $params)
    {
        $auth = new \Auth(new \DB\SQL\Mapper(\Registry::get('db'), '<TABLE>', array('login', 'password'), 60), array(
            'id' => 'login',
            'pw' => 'password'
        ));
        return $auth->basic(function() use ($auth, $f3) {
        });
    }

    /**
     * Check if we have a bearer access token, if so, set it in the f3 hive as access_token
     *
     * @return array $access_token the access token data
     */
    final protected function getBearerAccessToken()
    {
        $f3 = \Base::instance();

        // get the access token in order of preference
        foreach ($f3->get('SERVER') as $k => $header) {
            if (stristr($k, 'authorization') !== false) {
                if (preg_match('/Bearer\s+(?P<access_token>.+)$/i', $header, $matches)) {
                    $token = $matches['access_token'];
                }
            }
            break;
        }
        if (empty($token)) {
            $token = $f3->get('POST.access_token');
        }
        if (empty($token)) {
            $token = $f3->get('GET.access_token');
        }

        return $token;
    }


// unknown catch-all api method
    public function unknown($f3, $params)
    {
        $this->failure(4998, 'Unknown API Request', 400);
    }

// set relative URL
    protected function rel($path)
    {
        $f3 = \Base::instance();
        $this->data['rel'] = $f3->get('SCHEME').'://'.$f3->get('HOST').$path;

        return;
    }

// set canonical URL
    protected function href($path = null)
    {
        $f3 = \Base::instance();
        if (empty($path)) {
            $this->data['href'] = $f3->get('REALM');
        } else {
            $this->data['href'] = $f3->get('SCHEME').'://'.$f3->get('HOST').$path;
        }

        return;
    }

// route /api
    public function api($f3, $params)
    {
        $this->params['http_methods'] = 'GET,HEAD';
        $this->rel('/api');
    }
}

class ApiException extends \Exception {}
