<?php
namespace helpers;

/**
 * Base Helper Class
 *
 * @package helpers
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class Base extends \Prefab {
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
        $this->logger = \Registry::get('logger');
    }
}
