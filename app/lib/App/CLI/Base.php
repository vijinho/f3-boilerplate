<?php

namespace App\CLI;

use FFMVC\Helpers;
use App\{Traits, Controllers, Models, Mappers};

/**
 * Base CLI Controller Class.
 *
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
abstract class Base
{
    /**
     * @var object database class
     */
    protected $db;

    /**
     * Use climate by default
     *
     * @var object cli-handling class
     * @link http://climate.thephpleague.com/
     */
    protected $cli;


    /**
     * initialize.
     */
    public function __construct($params = [])
    {
        if (PHP_SAPI !== 'cli') {
            exit("This controller can only be executed in CLI mode.");
        }

        $f3 = \Base::instance();

        // inject class members based on params
        foreach ($params as $k => $v) {
            $this->$k = $v;
        }

        // if we have none of the following setup, use defaults
        if (empty($this->db)) {
            $this->db = \Registry::get('db');
        }

        if (empty($this->notificationObject)) {
            $this->notificationObject = Helpers\Notifications::instance();
        }

        if (empty($this->logObject)) {
            $this->logObject = \Registry::get('logger');
        }

        if (empty($this->cli)) {
            $this->cli = new \League\CLImate\CLImate;
            $this->cli->clear();
        }
    }

    /**
     * @param \Base $f3
     * @param array $params
     * @return void
     */
    public function beforeRoute($f3, array $params)
    {
        $cli = $this->cli;
        $cli->blackBoldUnderline("CLI Script");
    }


    /**
     * @param \Base $f3
     * @param array $params
     * @return void
     */
    public function afterRoute($f3, array $params)
    {
        $cli = $this->cli;
        $cli->shout('Finished.');
        $cli->info('Script executed in ' . round(microtime(true) - $f3->get('TIME'), 3) . ' seconds.');
        $cli->info('Memory used ' . round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB.');
    }
}
