<?php

namespace App\Controllers;

use FFMVC\Helpers as Helpers;

/**
 * Index Controller Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Index extends Base
{
    /**
     * Main homepage controller
     *
     * @alias home - f3 route alias
     * @param \Base $f3
     * @param array $params
     * @return void
     */
    public function index(\Base $f3, array $params = [])
    {
        Helpers\Notifications::instance()->add('Hello world!', 'success');
        echo \View::instance()->render('index/index.phtml');
    }
}
