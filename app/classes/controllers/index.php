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

    // render a php template .phtml view from ui/
    public function index() {
        echo \View::instance()->render('views/index/index.phtml');
    }

    // output some data as json with appropriate headers
    public function json() {
        \helpers\Response::json(array(
            'name' => 'Vijay',
            'random' => \helpers\String::random(5),
            'httpdatetime' => \helpers\Time::http(),
            'dbtimestamp' => \helpers\Time::db()
        ), array('ttl' => 600));
    }

}
