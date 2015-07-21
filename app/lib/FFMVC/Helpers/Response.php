<?php

namespace FFMVC\Helpers;

/**
 * HTTP Response Helper Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Response extends \Prefab
{
    /**
     * Encode the input parameter $data as JSON and output it with appropriate http headers.
     *
     * @param mixed $data   input variable, takes origin, age, methods
     * @param array $params parameters for the http headers: ttl, origin, methods (GET, POST, PUT, DELETE)
     * @param bool  $output send the output headers and body? or return them?
     *
     * @return array (array headers, string body)
     *
     * @see http://www.w3.org/TR/2008/WD-access-control-20080912/
     */
    public static function json($data, $params = array(), $output = true)
    {
        $f3 = \Base::instance();

        $headers = array();

        $headers['Content-type'] = 'application/json; charset=utf-8';
        $ttl = array_key_exists('ttl', $params) ? $params['ttl'] : 0; // cache for $ttl seconds
        if (empty($ttl)) {
            $headers['Cache-Control'] = 'no-store, no-cache, must-revalidate, max-age=0';
            $ttl = 0;
        }

        $headers['Expires'] = \FFMVC\Helpers\Time::http(array_key_exists('expires', $params) ? $params['expires'] : time() + $ttl);
        $headers['Access-Control-Max-Age'] = $ttl;

        if (array_key_exists('http_methods', $params)) {
            $headers['Access-Control-Allow-Methods'] = $params['http_methods'];
        } else {
            $headers['Access-Control-Allow-Methods'] = 'OPTIONS, HEAD, GET, POST, PUT, PATCH, DELETE';
        }

        if (array_key_exists('acl_origin', $params)) {
            $headers['Access-Control-Allow-Origin'] = $params['acl_origin'];
        } else {
            $headers['Access-Control-Allow-Origin'] = '*';
        }

        if (array_key_exists('acl_credentials', $params)) {
            $headers['Access-Control-Allow-Credentials'] = $params['credentials'];
        } else {
            $headers['Access-Control-Allow-Credentials'] = 'false';
        }

        $body = json_encode($data, JSON_PRETTY_PRINT);
        if (!empty($output)) {
            $headers['Content-Length'] = strlen($body);
        }
        if (array_key_exists('etag', $params)) {
            $headers['ETag'] = $params['etag'];
        } else {
            $headers['ETag'] = md5($body);
        }

        if (empty($output)) {
            return array('headers' => $headers, 'body' => $body);
        } else {
            // send the headers + data
            foreach ($headers as $header => $value) {
                header($header.': '.$value);
            }
            // default status is 200 - OK
            if (!array_key_exists('http_status', $params)) {
                $params['http_status'] = 200;
            }
            $f3->status($params['http_status']);
            $method = $f3->get('SERVER.REQUEST_METHOD');
            switch ($method) {
                case 'HEAD':
                    break;
                default:
                case 'GET':
                case 'PUT':
                case 'POST':
                case 'DELETE':
                    echo $body;
            }
        }
    }

    /**
     * Converts an array to XML.
     *
     * @param array            $array
     * @param SimpleXMLElement $xml
     * @param string           $child_name
     *
     * @return SimpleXMLElement $xml
     * @url http://stackoverflow.com/questions/1397036/how-to-convert-array-to-simplexml
     */
    private static function arrayToXML($array, \SimpleXMLElement $xml, $child_name)
    {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                (is_int($k)) ? self::arrayToXML($v, $xml->addChild($child_name), $v) : self::arrayToXML($v, $xml->addChild(strtolower($k)), $child_name);
            } else {
                (is_int($k)) ? $xml->addChild($child_name, $v) : $xml->addChild(strtolower($k), $v);
            }
        }

        return $xml->asXML();
    }

    /**
     * Encode the input parameter $data as XML and output it with appropriate http headers.
     *
     * @param mixed $data   input variable, takes origin, age, methods
     * @param array $params parameters for the http headers: ttl, origin, methods (GET, POST, PUT, DELETE)
     * @param bool  $output send the output headers and body? or return them?
     *
     * @return array (array headers, string body)
     *
     * @see http://www.w3.org/TR/2008/WD-access-control-20080912/
     */
    public static function xml($data, $params = array(), $output = true)
    {
        $f3 = \Base::instance();

        $headers = array();

        $headers['Content-type'] = 'application/xml; charset=utf-8';
        $ttl = array_key_exists('ttl', $params) ? $params['ttl'] : 0; // cache for $ttl seconds
        if (empty($ttl)) {
            $headers['Cache-Control'] = 'no-store, no-cache, must-revalidate, max-age=0';
            $ttl = 0;
        }

        $headers['Expires'] = \FFMVC\Helpers\Time::http(array_key_exists('expires', $params) ? $params['expires'] : time() + $ttl);
        $headers['Access-Control-Max-Age'] = $ttl;

        if (array_key_exists('http_methods', $params)) {
            $headers['Access-Control-Allow-Methods'] = $params['http_methods'];
        } else {
            $headers['Access-Control-Allow-Methods'] = 'OPTIONS, HEAD, GET, POST, PUT, PATCH, DELETE';
        }

        if (array_key_exists('acl_origin', $params)) {
            $headers['Access-Control-Allow-Origin'] = $params['acl_origin'];
        } else {
            $headers['Access-Control-Allow-Origin'] = '*';
        }

        if (array_key_exists('acl_credentials', $params)) {
            $headers['Access-Control-Allow-Credentials'] = $params['credentials'];
        } else {
            $headers['Access-Control-Allow-Credentials'] = 'false';
        }

        $body = self::arrayToXML($data, new \SimpleXMLElement('<root/>'), 'child_name_to_replace_numeric_integers');
        if (!empty($output)) {
            $headers['Content-Length'] = strlen($body);
        }
        if (array_key_exists('etag', $params)) {
            $headers['ETag'] = $params['etag'];
        } else {
            $headers['ETag'] = md5($body);
        }

        if (empty($output)) {
            return array('headers' => $headers, 'body' => $xml);
        } else {
            // send the headers + data
            foreach ($headers as $header => $value) {
                header($header.': '.$value);
            }
            // default status is 200 - OK
            if (!array_key_exists('http_status', $params)) {
                $params['http_status'] = 200;
            }
            $f3->status($params['http_status']);
            $method = $f3->get('SERVER.REQUEST_METHOD');
            switch ($method) {
                case 'HEAD':
                    break;
                default:
                case 'GET':
                case 'PUT':
                case 'POST':
                case 'DELETE':
                    echo $body;
            }
        }
    }
}
