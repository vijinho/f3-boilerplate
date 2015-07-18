<?php

namespace FFMVC\Controllers\Api;

/**
 * Api Controller Class.
 *
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Api {

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
     *     - 405 - method no allowed
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
     * response helper
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
     * initialize 
     */
    public function __construct() {
        $f3 = \Base::instance();
        $this->db = \Registry::get('db');
        $this->response = \FFMVC\Helpers\Response::instance();
        $params = $f3->get('PARAMS');
        $this->params['ttl'] = $f3->get('api.ttl');
        $this->api($f3, $params);
    }

    /**
     * authorize all incoming requests.
     */
    public function beforeRoute($f3, $params) {
        // api call does not need authorization
        if ($params[0] != '/api') {
            if (!$this->authorize($f3, $params)) {
                
            }
        }
    }

    /**
     * compile and send the json response.
     */
    public function afterRoute($f3, $params) {
        $version = (int) $f3->get('GET.version');
        if (empty($version)) {
            $version = $this->version;
        }
        if ($version !== $this->version) {
            $this->failure(4000, 'Unknown API version requested.', 400);
        }
        if (empty($this->data['href'])) {
            $data['href'] = $this->href();
        }
        $data = array(
            'service' => 'API',
            'api' => $version,
//            'protocol' => $f3->get('SCHEME'),
        ) + $this->data;
        if (count($this->errors)) {
            ksort($this->errors);
            $data['errors'] = $this->errors;
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
     * @param int    $code    the error code
     * @param string $message the error message
     * @param int    $http_status the http status code
     */
    public function failure($code, $message, $http_status = null) {
        $this->errors[$code] = $message;
        if (!empty($http_status)) {
            $this->params['http_status'] = 400;
        }
    }

    /**
     * authenticate and check account_group of user.
     *
     * @return bool true/false if authenticated successfully
     */
    public function authorize($f3, $params) {
        try {
            
        } catch (\Exception $e) {
            // set failure message for user
            switch ($e->getCode()) {
                
            }

            return false;
        }

        return true;
    }

// unknown catch-all api method
    public function unknown($f3, $params) {
        $this->failure('4001', 'Unknown API Request', 400);
    }

// set relative URL    
    protected function rel($path) {
        $this->data['rel'] = \FFMVC\Helpers\Url::base() . $path;
        return;
    }

// set canonical URL    
    protected function href($path = null) {
        if (empty($path)) {
            $f3 = \Base::instance();
            $this->data['href'] = $f3->get('REALM');
        } else {
            $this->data['href'] = \FFMVC\Helpers\Url::base() . $path;
        }
        return;
    }
    
// route /api
    public function api($f3, $params) {
        $this->params['http_methods'] = 'GET,HEAD';
        $this->rel('/api');
    }

}
