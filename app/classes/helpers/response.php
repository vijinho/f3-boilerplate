<?php
namespace helpers;

/**
 * HTTP Response Helper Class
 *
 * @package helpers
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class Response extends \Prefab {

    /**
     * Encode the input parameter $x as JSON and output it with appropriate http headers
     *
     * @param mixed $x input variable, takes origin, age, methods
     * @param array $params parameters for the http headers: ttl, origin, methods (GET, POST, PUT, DELETE)
     * @see http://www.w3.org/TR/2008/WD-access-control-20080912/
     */
    public static function json($x, $params = array()) {
        header('Content-type: application/json');

        $ttl = (array_key_exists('ttl', $params) ? $params['ttl'] : \f3::instance()->get('ajax.ttl')); // cache for $ttl seconds
        if (empty($ttl)) {
            header('Cache-Control: no-cache, must-revalidate');
            $ttl = 0;
        }

        header('Expires:  ' . \helpers\time::http(array_key_exists('expires', $params) ? $params['expires'] : time() + $ttl));

        header('Access-Control-Max-Age: ' . $ttl);

        if (array_key_exists('origin', $params)) {
            header('Access-Control-Allow-Origin: ' . $params['origin']);
        }

        if (array_key_exists('methods', $params)) {
            header('Access-Control-Allow-Methods: ' .  $methods);
        }

        echo json_encode($x, (\f3::instance()->get('DEBUG') ? JSON_PRETTY_PRINT : null));
    }
}
