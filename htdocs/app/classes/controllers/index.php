<?php
namespace controllers;

/**
 * Index Controller Class
 *
 * @package controllers
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.phtmll)
 */

class Index extends Base {

    public function __construct() {
        parent::__construct();
    }

    public function home() {
        echo \View::instance()->render('views/index/home.phtml');
    }

    public function about() {
        echo \View::instance()->render('views/index/about.phtml');
    }

    public function contact() {
        echo \View::instance()->render('views/index/contact.phtml');
    }

    public function credits() {
        echo \View::instance()->render('views/index/credits.phtml');
    }

}
