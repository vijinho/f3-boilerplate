<?php
namespace controllers;

/**
 * Index Controller Class
 *
 * @package controllers
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class Index extends Base {

    public function __construct() {
        parent::__construct();
    }

    public function home() {
        echo \View::instance()->render('views/index/home.htm');
    }

    public function about() {
        echo \View::instance()->render('views/index/about.htm');
    }

    public function contact() {
        echo \View::instance()->render('views/index/contact.htm');
    }

    public function credits() {
        echo \View::instance()->render('views/index/credits.htm');
    }

}
