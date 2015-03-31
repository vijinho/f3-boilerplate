<?php
namespace FFMVC\Controllers;

/**
 * Index Controller Class
 *
 * @package FFMVC\Controllers
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class Index
{
    // render a php template .phtml view from ui/
    final public function index($f3, $params) 
    {
        echo \View::instance()->render('views/index/index.phtml');
    }
}
