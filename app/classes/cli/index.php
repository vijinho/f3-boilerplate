<?php
namespace cli;

/**
 * Index CLI Class
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

    public function index() {
        echo "CLI\n" . "Some random string:" .  \helpers\String::random(255) . "\n";
    }
}
