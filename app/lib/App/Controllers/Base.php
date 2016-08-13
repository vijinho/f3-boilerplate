<?php

namespace App\Controllers;

use FFMVC\Helpers;

/**
 * Base Controller Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
abstract class Base
{
    /**
     * initialize.
     */
    public function __construct($params = [])
    {
        $f3 = \Base::instance();

        if (!array_key_exists('loggerObject', $params)) {
            $this->loggerObject = \Registry::get('logger');
        }

        if (!array_key_exists('notificationObject', $params)) {
            $this->notificationObject = Helpers\Notifications::instance();
        }

        // inject class members
        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
    }

}
