<?php

namespace App\Controllers\API;

use FFMVC\Helpers;


/**
 * Api Controller Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class API
{
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
     * @var array errors
     */
    protected $errors = [];

    /**
     * response data.
     *
     * @var array data
     */
    protected $data = [];

    /**
     * response params.
     *
     * @var array params
     */
    protected $params = [];

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
            'status' => 400
        ],
        'invalid_credentials' => [
            'code' => 'invalid_credentials',
            'description' => 'Credentials for authentication were invalid.',
            'uri' => '',
            'state' => '',
            'status' => 403
        ],
        'invalid_client' => [
            'code' => 'invalid_client',
            'description' => 'Client authentication failed (e.g., unknown client, no client authentication included, or unsupported authentication method).',
            'uri' => '',
            'state' => '',
            'status' => 401
        ],
        'invalid_grant' => [
            'code' => 'invalid_grant',
            'description' => 'The provided authorization grant (e.g., authorization code, resource owner credentials) or refresh token is invalid, expired, revoked, does not match the redirection URI used in the authorization request, or was issued to another client.',
            'uri' => '',
            'state' => '',
            'status' => 401
        ],
        'unsupported_grant_type' => [
            'code' => 'unsupported_grant_type',
            'description' => 'The authorization grant type is not supported by the authorization server.',
            'uri' => '',
            'state' => '',
            'status' => 400
        ],
        'unauthorized_client' => [
            'code' => 'unauthorized_client',
            'description' => 'The client is not authorized to request an authorization code using this method.',
            'uri' => '',
            'state' => '',
            'status' => 401
        ],
        'access_denied' => [
            'code' => 'access_denied',
            'description' => 'The resource owner or authorization server denied the request.',
            'uri' => '',
            'state' => '',
            'status' => 400
        ],
        'unsupported_response_type' => [
            'code' => 'unsupported_response_type',
            'description' => 'The authorization server does not support obtaining an authorization code using this method.',
            'uri' => '',
            'state' => '',
            'status' => 400
        ],
        'invalid_scope' => [
            'code' => 'invalid_scope',
            'description' => 'The requested scope is invalid, unknown, or malformed.',
            'uri' => '',
            'state' => '',
            'status' => 400
        ],
        'server_error' => [
            'code' => 'server_error',
            'description' => 'The authorization server encountered an unexpected condition that prevented it from fulfilling the request.',
            'uri' => '',
            'state' => '',
            'status' => 500
        ],
        'temporarily_unavailable' => [
            'code' => 'temporarily_unavailable',
            'description' => 'The authorization server is currently unable to handle the request due to a temporary overloading or maintenance of the server.',
            'uri' => '',
            'state' => '',
            'status' => 400
        ],
    ];

    /**
     * The OAuth Error to return if an OAuthError occurs.
     *
     * @var boolean|array OAuthError
     */
    protected $OAuthError = null;

    /**
     * initialize
     */
    public function __construct()
    {
        $this->params['http_status'] = 200;
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
        $this->params['headers']['Version'] = $f3->get('api.version');

        // if an OAuthError is set, return that too
        $data = [];
        if (!empty($this->OAuthError)) {
            $data['error'] = $this->OAuthError;
        }

        if (count($this->errors)) {
            foreach ($this->errors as $code => $message) {
                $data['error']['errors'][] = [
                    'code' => $code,
                    'message' => $message
                ];
            }
            ksort($this->errors);
        }

        Helpers\Response::json(array_merge($data, $this->data), $this->params);
    }

    /**
     * add to the list of errors that occured during this request.
     *
     * @param string $code        the error code
     * @param string $message     the error message
     * @param null|int    $http_status the http status code
     * @return void
     */
    public function failure(string $code, string $message, int $http_status = null)
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
     * @return array|bool error type or boolean false
     */
    protected function getOAuthErrorType(string $type)
    {
        return array_key_exists($type, $this->OAuthErrorTypes) ? $this->OAuthErrorTypes[$type] : false;
    }

    /**
     * Set the RFC-compliant OAuth Error to return.
     *
     * @param string $code  of error code from RFC
     * @return array|boolean the OAuth error array
     */
    public function setOAuthError(string $code)
    {
        $this->OAuthError = $this->getOAuthErrorType($code);

        // only set https status if not set anywhere else
        if ($this->params['http_status'] == 200) {
            $this->params['http_status'] = $this->OAuthError['status'];
        }

        return $this->OAuthError;
    }

    /**
     * Basic Authentication for email:password
     *
     * Check that the credentials match the database
     * Cache result for 30 seconds.
     *
     * @return bool success/failure
     */
    public function basicAuthenticateLoginPassword(): bool
    {
        $auth = new \Auth(new \DB\SQL\Mapper(\Registry::get('db'), 'users', ['email', 'password'], 30), [
            'id' => 'email',
            'pw' => 'password',
        ]);

        return (bool) $auth->basic(function ($pw) {
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
    public function authenticateClientIdSecret(string $clientId, string $clientSecret): bool
    {
        if (empty($clientId) || empty($clientSecret)) {
            return false;
        }
        // validate client_id/secret
        return false;
    }

    /**
     * Basic Authentication for client_id:client_secret
     *
     * Check that the credentials match a registered app
     *
     * @return bool success/failure
     */
    public function basicAuthenticateClientIdSecret(): bool
    {
        $f3 = \Base::instance();
        return $this->authenticateClientIdSecret($f3->get('REQUEST.PHP_AUTH_USER'), $f3->get('REQUEST.PHP_AUTH_PW'));
    }

    /**
     * Validate the provided access token or get the bearer token from the incoming http request
     * do $f3->set('access_token') if OK.
     *
     * Or login using app token with HTTP Auth using one of
     *
     * email:password
     * email:access_token
     *
     * Or by URL query string param - ?access_token=$access_token
     *
     * Sets hive vars: user[] (mandatory), api_app[] (optional) and userScopes[]
     *
     * @return null|boolean true/false on valid access credentials
     */
    protected function validateAccess()
    {
        $f3 = \Base::instance();

        // return if forcing access to https and not https
        if ('http' == $f3->get('SCHEME') && !empty($f3->get('api.https'))) {
            $this->failure('api_connection_error', "Connection only allowed via HTTPS!", 400);
            $this->setOAuthError('unauthorized_client');

            return false;
        }

        // get token from request to set the user and app
        // override if anything in basic auth or client_id/secret AFTER
        $token = $f3->get('REQUEST.access_token');
        if (!empty($token)) {
            // token does not exist!
            $tokenExists = null; // perform a real check here against your db!
            if (null == $tokenExists) {
                $this->failure('authentication_error', "The token does not exist!", 401);
                $this->setOAuthError('invalid_grant');

                return false;
            }
            // check token is not out-of-date
            $expires = 0; // get the expiry from your db!
            if (time() > $expires) {
                $this->failure('authentication_error', "The token expired!", 401);
                $this->setOAuthError('invalid_grant');

                return false;
            }
            // if token found load the user for the token from your db!
        }

        // login with client_id and client_secret in request OR login/password using basic auth
        if ($f3->get('REQUEST.PHP_AUTH_USER')) {
            if ($this->basicAuthenticateClientIdSecret()) {
            } elseif ($this->basicAuthenticateLoginPassword()) {
            }
        }

        $userAuthenticated = false; // do a proper check here based on the above
        if (!$userAuthenticated) {
            $this->failure('authentication_error', "Not possible to authenticate the request.", 400);
            $this->setOAuthError('invalid_credentials');

            return false;
        }

        return true;
    }

    /**
     * catch-all
     *
     * @return void
     */
    public function unknown()
    {
        $this->setOAuthError('invalid_request');
        $this->failure('api_connection_error', 'Unknown API Request', 400);
    }

}
