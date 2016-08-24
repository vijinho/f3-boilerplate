<?php

namespace App\Controllers\API;

use App\Models as Models;
use FFMVC\Helpers as Helpers;

/**
 * API Controller Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class API
{
    /**
     * version.
     *
     * @var version
     */
    protected $version;

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
    protected $errors = [];

    /**
     * response data.
     *
     * @var data
     */
    protected $data = [];

    /**
     * response params.
     *
     * @var params
     */
    protected $params = [];

    /**
     * response helper object.
     *
     * @var response
     */
    protected $responseObject;

    /**
     * logger object.
     *
     * @var response
     */
    protected $loggerObject;

    /**
     * db.
     *
     * @var db
     */
    protected $db;

    /**
     * Error format required by RFC6794.
     *
     * @var type
     * @link https://tools.ietf.org/html/rfc6749
     */
    protected $OAuthErrorTypes = [
        'invalid_request' => [
            'code' => 'invalid_request',
            'description' => 'The request is missing a required parameter, includes an invalid parameter value, includes a parameter more than once, or is otherwise malformed.',
            'uri' => '',
            'state' => '',
        ],
        'invalid_credentials' => [
            'code' => 'invalid_credentials',
            'description' => 'Credentials for authentication were invalid.',
            'uri' => '',
            'state' => '',
        ],
        'invalid_client' => [
            'code' => 'invalid_client',
            'description' => 'Client authentication failed (e.g., unknown client, no client authentication included, or unsupported authentication method).',
            'uri' => '',
            'state' => '',
        ],
        'invalid_grant' => [
            'code' => 'invalid_grant',
            'description' => 'The provided authorization grant (e.g., authorization code, resource owner credentials) or refresh token is invalid, expired, revoked, does not match the redirection URI used in the authorization request, or was issued to another client.',
            'uri' => '',
            'state' => '',
        ],
        'unsupported_grant_type' => [
            'code' => 'unsupported_grant_type',
            'description' => 'The authorization grant type is not supported by the authorization server.',
            'uri' => '',
            'state' => '',
        ],
        'unauthorized_client' => [
            'code' => 'unauthorized_client',
            'description' => 'The client is not authorized to request an authorization code using this method.',
            'uri' => '',
            'state' => '',
        ],
        'access_denied' => [
            'code' => 'access_denied',
            'description' => 'The resource owner or authorization server denied the request.',
            'uri' => '',
            'state' => '',
        ],
        'unsupported_response_type' => [
            'code' => 'unsupported_response_type',
            'description' => 'The authorization server does not support obtaining an authorization code using this method.',
            'uri' => '',
            'state' => '',
        ],
        'invalid_scope' => [
            'code' => 'invalid_scope',
            'description' => 'The requested scope is invalid, unknown, or malformed.',
            'uri' => '',
            'state' => '',
        ],
        'server_error' => [
            'code' => 'server_error',
            'description' => 'The authorization server encountered an unexpected condition that prevented it from fulfilling the request.',
            'uri' => '',
            'state' => '',
        ],
        'temporarily_unavailable' => [
            'code' => 'temporarily_unavailable',
            'description' => 'The authorization server is currently unable to handle the request due to a temporary overloading or maintenance of the server.',
            'uri' => '',
            'state' => '',
        ],
    ];

    /**
     * The OAuth Error to return if an OAuthError occurs.
     *
     * @var bool|array OAuthError
     */
    protected $OAuthError = null;

     /**
      * init
      * @param \Base $f3
      */
    public function __construct($f3)
    {
        $this->db = \Registry::get('db');
        $this->version = $f3->get('api.version');
        $this->responseObject = Helpers\Response::instance();
        $this->loggerObject = \Registry::get('logger');
    }

     /**
      * compile and send the json response.
      *
      * @param \Base $f3
      * @return void
      */
    public function afterRoute(\Base $f3)
    {
        $this->params['headers'] = empty($this->params['headers']) ? [] : $this->params['headers'];
        $this->params['headers'] = [
            'Version' => $f3->get('app.api_version'),
        ];

        $data = [];

        // if an OAuthError is set, return that too
        if (!empty($this->OAuthError)) {
            $data['error'] = $this->OAuthError;
        }

        if (count($this->errors)) {
            ksort($this->errors);
            foreach ($this->errors as $code => $message) {
                $data['error']['errors'][] = [
                    'code' => $code,
                    'message' => $message
                ];
            }
        }

        if (is_array($this->data)) {
            $data = array_merge($data, $this->data);
        }

        $this->responseObject->json($data, $this->params);
    }

    /**
     * add to the list of errors that occured during this request.
     *
     * @param string $code        the error code
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
     * Get OAuth Error Type.
     *
     * @param string $type
     *
     * @return mixed array error type or boolean false
     */
    protected function getOAuthErrorType(string $type)
    {
        return array_key_exists($type, $this->OAuthErrorTypes) ? $this->OAuthErrorTypes[$type] : false;
    }

    /**
     * Set the RFC-compliant OAuth Error to return.
     *
     * @param string $code  of error code from RFC
     * @throws Models\APIServerException
     * @return the OAuth error array
     */
    public function setOAuthError(string $code)
    {
        $error = $this->getOAuthErrorType($code);
        if (empty($error)) {
            throw new APIServerException('Invalid OAuth error type.', 5100);
        } else {
            $this->OAuthError = $error;
            // only set https status if not set anywhere else
            if (!empty($this->params['http_status']) && $this->params['http_status'] !== 200) {
                return $error;
            }

            switch ($code) {

                case 'invalid_client': // as per-spec
                case 'invalid_grant':
                case 'unauthorized_client':
                    $this->params['http_status'] = 401;
                    break;

                case 'server_error':
                    $this->params['http_status'] = 500;
                    break;

                case 'invalid_credentials':
                    $this->params['http_status'] = 403;
                    break;

                default:
                    $this->params['http_status'] = 400;
                    break;
            }
        }

        return $error;
    }

    /**
     * Basic Authentication for email:password
     *
     * Check that the credentials match the database
     * Cache result for 30 seconds.
     *
     * @return bool success/failure
     */
    public function basicAuthenticateLoginPassword(): boolean
    {
        $auth = new \Auth(new \DB\SQL\Mapper(\Registry::get('db'), 'users', ['email', 'password'], 30), [
            'id' => 'email',
            'pw' => 'password',
        ]);

        return (int) $auth->basic(function ($pw) {
            return Helpers\Str::password($pw);
        });
    }

    /**
     * Authentication for client_id and client_secret
     *
     * Check that the credentials match a registered app
     * @param string $clientId the client id to check
     * @param string $clientSecret the client secret to check
     * @return bool success/failure
     */
    public function authenticateClientIdSecret($clientId, $clientSecret): boolean
    {
        if (empty($clientId) || empty($clientSecret)) {
            return false;
        }
        // check fields, return boolean
    }

    /**
     * Basic Authentication for client_id:client_secret
     *
     * Check that the credentials match a registered app
     *
     * @return bool success/failure
     */
    public function basicAuthenticateClientIdSecret(): boolean
    {
        $f3 = \Base::instance();
        return $this->authenticateClientIdSecret($f3->get('REQUEST.PHP_AUTH_USER'), $f3->get('REQUEST.PHP_AUTH_PW'));
    }

    /**
     * Validate the provided access token or get the bearer token from the incoming http request
     *
     * @return null|boolean true/false on valid access credentials
     */
    protected function validateAccess()
    {
        $f3 = \Base::instance();

        // if forcing access to https die
        if ('http' == $f3->get('SCHEME') && !empty($f3->get('api.https'))) {
            $this->failure('api_connection_error', "Connection only allowed via HTTPS!", 400);
            $this->setOAuthError('unauthorized_client');
            return;
        }

        $userAuthenticated = false;
        $token = $f3->get('REQUEST.access_token');
        if (!empty($token)) {
            // fetch token and check
            //$userAuthenticated = true;
        }

        // login with client_id and client_secret in request
        $clientId = $f3->get('REQUEST.client_id');
        $clientSecret = $f3->get('REQUEST.client_secret');
        if (!empty($clientId) && !empty($clientSecret)
                && $this->authenticateClientIdSecret($clientId, $clientSecret)) {
            $userAuthenticated = true;
        }

        // check if login via http basic auth
        $phpAuthUser = $f3->get('REQUEST.PHP_AUTH_USER');
        if (!empty($phpAuthUser)) {
            // try to login as email:password
            if ($this->basicAuthenticateLoginPassword()) {
                $userAuthenticated = true;
            } elseif ($this->basicAuthenticateClientIdSecret()) {
                $userAuthenticated = true;
            }
        }

        if (!$userAuthenticated) {
            $this->failure('authentication_error', "Not possible to authenticate the request.", 400);
            $this->setOAuthError('invalid_credentials');
            return false;
        }

        return true;
    }

    /**
     * unknown catch-all api method
     * @return void
     */
    public function unknown()
    {
        $this->setOAuthError('invalid_request');
        $this->failure('api_connection_error', 'Unknown API Request', 400);
    }
}

class APIServerException extends \Exception
{
};
