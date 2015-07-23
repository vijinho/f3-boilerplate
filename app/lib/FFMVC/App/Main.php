<?php

namespace FFMVC\App;

/**
 * Main App Class
 *
 * This should be included and run by every app upon initialisation and termination
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Main extends \Prefab
{
    const APP_VERSION = '1.6';

    /**
     * setup the base application environment.
     *
     * @param object $logger inject your own logger
     */
    final public static function start(&$f3, $logger = null)
    {
        // http://php.net/manual/en/function.ob-start.php
        ob_start();

        // read config and overrides
        // @see http://fatfreeframework.com/framework-variables#configuration-files
        $f3->config('config/default.ini');
        if (file_exists('config/config.ini')) {
            $f3->config('config/config.ini');
        }

        $debug = $f3->get('debug');

        // setup application logging
        if (empty($logger)) {
            $logfile = $f3->get('application.logfile');
            if (!empty($logfile)) {
                $logger = new \Log($logfile);
            }
        }
        $f3->set('logger', $logger);

        // setup outgoing email server for php mail command
        ini_set('SMTP', $f3->get('email.host'));
        ini_set('sendmail_from', $f3->get('email.from'));

        return $f3;
    }

    /**
     * final tasks for the application once run.
     */
    final public static function finish(&$f3)
    {
        // log script execution time if debugging
        $debug = $f3->get('DEBUG');
        $logger = &$f3->ref('logger');

        if ($logger && $debug || $f3->get('application.environment') == 'development') {
            // log database transactions if level 3
            $db = \Registry::get('db');    
            if ($debug == 3 && 
                method_exists($logger, 'write') &&
                method_exists($db, 'log')) 
            {
                $logger->write($db->log());
            }
            $execution_time = round(microtime(true) - $f3->get('TIME'), 3);
            $params = $f3->get('PARAMS');
            $logger->write('Script '.$params[0].' executed in '.$execution_time.' seconds using '.
                round(memory_get_usage() / 1024 / 1024, 2).'/'.
                round(memory_get_peak_usage() / 1024 / 1024, 2).' MB memory/peak');
        }

        // http://php.net/manual/en/function.ob-end-flush.php
        while (ob_get_level()) {
            ob_end_flush();
            flush();
        }
    }
}
