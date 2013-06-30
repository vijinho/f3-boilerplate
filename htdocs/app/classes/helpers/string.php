<?php
namespace helpers;

/**
 * Base Helper Class
 *
 * @package helpers
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class String extends base {

    /**
    * initialize controller
    *
    * @return void
    */
    public function __construct() {
        parent::__construct();
    }

    /**
     * random string producer
     *
     * @example $string = \helpers\String::instance()->randomString();
     * @author Sascha Ohms
     * @author Philipp Hirsch
     * @copyright Copyright 2011, Bugtrckr-Team
     * @license http://www.gnu.org/licenses/lgpl.txt
     */
    public static function randomString($length = 8)
    {
        return substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length);
    }
}
