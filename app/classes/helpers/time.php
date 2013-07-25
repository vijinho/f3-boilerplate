<?php
namespace helpers;

/**
 * Time and Date Helper Class
 *
 * @package helpers
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class Time extends \Prefab {

    /**
     * format a database-specific date/time string
     *
     * @param optional int $unixtime the unix time (null = now)
     * @param optional string $dbms the database software the timestamp is for
     * @return string date in format of database driver
     * @todo add a switch for the f3 database driver and set the timestamp
     */
    public static function db($unixtime = null, $dbms = null) {

        // use current time if bad time value or unset
        $unixtime = (int) $unixtime;
        if ($unixtime <= 0) {
            $unixtime = time();
        }

        // format date/time according to database driver
        $dbms = (empty($dbms)) ? \F3::get('db.driver') : $dbms;
        switch ($dbms) {
            default:
            case 'mysql':
                return date('Y-m-d H:i:s', $unixtime);
        }
    }

    /**
     * Utility to convert timestamp into a http header date/time
     *
     * @param int time php time value
     * @param string $zone timezone, default GMT
     */
    public static function http($unixtime = null, $zone = 'GMT') {

        // use current time if bad time value or unset
        $unixtime = (int) $unixtime;
        if ($unixtime <= 0) {
            $unixtime = time();
        }

        // if its not a 3 letter timezone set it to GMT
        if (strlen($zone) != 3) {
            $zone = 'GMT';
        } else {
            $zone = strtoupper($zone);
        }

        return gmdate("D, d M Y H:i:s", $unixtime) . ' ' . $zone;
    }
}
