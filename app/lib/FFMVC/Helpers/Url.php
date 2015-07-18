<?php

namespace FFMVC\helpers;

/**
 * URL Helper Class.
 *
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Url extends \Prefab
{
    /**
     * return the first part of the current url after the first slash
     * e.g /users/home returns users
     * Usage: \helpers\URL::basepath().
     *
     * @param optional bool $fullpath, set to true if you want an array of the full path
     *
     * @return string $basepath the first part of the url path, or the full (if param set to true)
     *
     * @author Daniel Persson
     */
    public static function basepath($fullpath = false)
    {
        $f3 = \F3::instance();
        $url = $f3->get('REALM');
        $parts = preg_split('/\//', $url);
        if (!empty($parts) && count($parts) >= 3) {
            if (!empty($fullpath)) {
                return $parts;
            } else {
                return $parts[3];
            }
        }

        return false;
    }

    /**
     * Get the base url of the website.
     *
     *
     * @return string $path the base website url
     *
     * @author Vijay Mahrra
     */
    public static function base()
    {
        $f3 = \F3::instance();
        if (preg_match("/(^http[s]?:\/\/[^\/]+)/i", $f3->get('REALM'), $matches)) {
            return $matches[1];
        }

        return false;
    }
}
