<?php

namespace FFMVC\Models;

/**
 * Base Model Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
abstract class Base extends \Prefab
{
    /**
     * @var object database class
     */
    protected $db;

    /**
     * @var object user messages class
     */
    protected $messages;

    /**
     * @var object logging class
     */
    protected $logger;

    /**
     * initialize with params, fallback to f3 values
     */
    public function __construct($params = array())
    {
        parent::__construct();

        $f3 = \Base::instance();
        foreach ($params as $k => $v) {
            $this->$k = $v;
        }
        if (empty($this->db)) {
            $this->db = \Registry::get('db');
        }
        if (empty($this->messages)) {
            $this->messages = Helpers\Messages::instance();
        }
        if (empty($this->logger)) {
            $this->logger = &$f3->ref('logger');
        }
    }
}
