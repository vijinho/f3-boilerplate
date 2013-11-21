<?php
namespace controllers;

/**
 * Base Controller Class
 *
 * @package controllers
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

abstract class Base extends \Prefab {

    /**
    * f3 instance
    *
    * @var f3
    */
    protected $f3;

    /**
    * f3 logger instance
    *
    * @var logger
    */
    protected $logger;

    /**
    * initialize controller
    *
    * @return void
    */
    public function __construct() {
        $this->f3 = \Base::instance();
        $this->logger = \Registry::get('logger');
    }
}
