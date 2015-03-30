<?php
namespace models;

/**
 * Base Model Class
 *
 * @package models
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
    * database connection
    *
    * @var db
    */
    protected $db;

    /**
    * f3 logger instance
    *
    * @var logger
    */
    protected $logger;

    /**
    * initialize model
    *
    * @return void
    */
    public function __construct($db = null, $logger  = null) {
        $this->f3 = \Base::instance();
        $this->db = empty($db) ? \Registry::get('db') : $db;
        $this->logger = empty($logger) ? \Registry::get('logger') : $logger;
    }
}
