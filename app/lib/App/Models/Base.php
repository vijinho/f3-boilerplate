<?php

namespace App\Models;

/**
 * Base Model Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
abstract class Base extends \Prefab
{
    /**
     * initialize with array of params, 'logger' can be injected
     */
    public function __construct($params = [])
    {
        $f3 = \Base::instance();

        if (!array_key_exists('logObject', $params)) {
            $this->logObject = \Registry::get('logger');
        }

        if (!array_key_exists('dbObject', $params)) {
            $this->dbObject = \Registry::get('db');
        }

        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
    }
}
