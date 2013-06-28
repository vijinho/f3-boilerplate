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

class Base extends \Prefab {

    /**
    * database connection
    *
    * @var db
    */
    protected $db;

    /**
    * initialize model
    *
    * @return void
    */
    public function __construct() {
        $this->db = \Registry::get('db');
    }
}