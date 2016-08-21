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
     * @var \Log log class
     */
    protected $logger;

    /**
     * @var \DB\SQL database class
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
    public function __construct(\Base $f3, array $params = [])
    {
        if (PHP_SAPI !== 'cli') {
            exit("This controller can only be executed in CLI mode.");
        }

        $this->db = \Registry::get('db');
        $this->logger = \Registry::get('logger');
        $this->cli = new \League\CLImate\CLImate;
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
