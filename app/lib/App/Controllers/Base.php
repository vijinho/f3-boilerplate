<?php

namespace App\Controllers;

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
     * Base WWW Controller
     *
     * @param \Base $f3
     * @param array $params
     */
    public function __construct(\Base $f3, array $params = [])
    {
    }
}
