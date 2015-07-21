<?php

namespace FFMVC\Models;

/**
 * Base Model Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
abstract class Base
{
    protected $db;
    protected $logger;

    /**
     * setup model, allow injection of different db and logger than f3
     *
     * @param (optional) object $db database object
     * @param (optional) object $logger logging object
     */
    public function __construct($db = null, $logger = null)
    {
        $f3 = \Base::instance();
        $this->db = empty($db) ? \Registry::get('db') : $db;
        $this->logger = empty($logger) ? &$f3->ref('logger') : $logger;
    }
}
