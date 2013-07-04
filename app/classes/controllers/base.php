<?php
namespace controllers;

/**
 * Base Controller Class
 *
 * @package controllers
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

abstract class Base {

    /**
    * f3 instance
    *
    * @var f3
    */
    protected $f3;

    /**
    * f3 logger instance
    *
    * @var logger
    */
    protected $logger;

    /**
    * initialize controller
    *
    * @return void
    */
    public function __construct() {
        $this->f3 = \F3::instance();
        $this->logger = \Registry::get('logger');
    }

    /*
     * Utility to convert timestamp into a http header date
     *
     * @param int time php time value
     * @param string $zone timezone, default GMT
     */
    protected function http_header_date($time = null, $zone = 'GMT') {
        // use current time if bad time value
        $time = (int) $time;
        if (empty($time) || $time < 0) {
            $time = null;
        }
        // if its not a 3 letter timezone set it to GMT
        $zone = strtoupper($zone);
        if (strlen($zone) != 3) {
            $zone = 'GMT';
        }
        return gmdate("D, M d Y H:i:s", $time) . ' ' . $zone;
    }

    /*
     * return the input paramater as a json output request
     *
     * @param mixed $x input variable, takes origin, age, methods
     * @see http://www.w3.org/TR/2008/WD-access-control-20080912/
     */
    protected function send_json($x, $params = array()) {
        $ttl = (array_key_exists('ttl', $params) ? $params['ttl'] : $this->f3->get('ajax.ttl')); // cache for $ttl seconds
        if (empty($ttl))
            header('Cache-Control: no-cache, must-revalidate');
        $methods = (array_key_exists('methods', $params) ? $params['methods'] : 'GET, POST'); // Don't allow PUT, DELETE by default
        header('Expires:  ' . self::http_header_date(array_key_exists('expires', $params) ? $params['expires'] : time() + $ttl));
        header('Content-type: application/json');
        if (array_key_exists('origin', $params))
            header('Access-Control-Allow-Origin: ' . $params['origin']);
        header('Access-Control-Max-Age: ' . $ttl);
        header('Access-Control-Allow-Methods: ' .  $methods);
        echo json_encode($x, ($this->f3->get('DEBUG') ? JSON_PRETTY_PRINT : null));
    }
}
