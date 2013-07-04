<?php
namespace models;

/**
 * Test Model Class
 *
 * @package models
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.phtmll)
 */

class Test extends Base {

    public function __construct() {
        parent::__construct();
    }

    // make sure you set your config.ini database settings for this to work
    public function show_tables() {
        try {
            $tables = $this->db->exec('SHOW TABLES');
            return $tables;
        } catch (Exception $e) {
            return $e;
        }
    }
}
