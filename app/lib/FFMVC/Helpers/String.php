<?php

namespace FFMVC\Helpers;

/**
 * String Helper Class
 *
 * @package helpers
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class String extends \Prefab
{
    /**
     * generate random string
     *
     * @param int $length of password
     * @param string $chars characters to use for random string
     * @return string password
     */
    final public static function random($length = 10, $chars = null)
    {
        if (empty($chars)) {
            $chars = '23456789abcdefghjkmnopqrstuvwxyz';
        }
        $chars = str_shuffle($chars); // shuffle base character string
        $x = strlen($chars) - 1;
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, rand(0, $x), 1);
        }
        return $str;
    }


    /**
     * Generates a hash for a given string
     *
     * @param string $string to salt
     * @param string $pepper string pepper to add to the salted string for extra security
     * @param string $salt string if not default application.salt config item
     * @return string $encoded
     * @url http://php.net/manual/en/function.hash-hmac.php
     * @url http://fatfreeframework.com/base#hash
     */
    final public static function salted($string, $pepper = '')
    {
        $f3 = \Base::instance();
        $salt = $f3->get('application.salt');
        $hash = $f3->get('application.hash');
        return base64_encode(hash_hmac($hash, $string, $salt . $pepper, true));
    }
}
