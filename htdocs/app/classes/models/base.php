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
    public function __construct() {
        $this->db = \Registry::get('db');
        $this->logger = \Registry::get('logger');
    }

    /*
     * format a database-specific date/time string
     *
     * @param optional int $timestamp the unix time (null = now)
     * @return string date in format of database driver
     * @todo add a switch for the f3 database driver and set the timestamp
     */
    public static function db_datetime($timestamp = null) {
        $timestamp = (int) $timestamp;
        return date('Y-m-d H:i:s', empty($timestamp) ? time() : $timestamp);
    }
}
