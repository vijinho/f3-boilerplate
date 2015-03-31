<?php
namespace FFMVC\App;

/**
 * Main App Class
 *
 * @package FFMVC\App
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2015 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

class Main extends \Prefab 
{
    const APP_VERSION = '1.3';
            
    /**
    * setup the base application environment
    *
    * @return void
    */
    final public static function start(&$f3)
    {
        // read config and overrides
        // @see http://fatfreeframework.com/framework-variables#configuration-files
        $f3->config('config/default.ini');
        if (file_exists('config/config.ini'))
            $f3->config('config/config.ini');
        
        $debug = $f3->get('debug');
        
        // setup application logging
        $logfile = $f3->get('application.logfile');
        if (!empty($logfile)) {
            $logger = new \Log($logfile);
            $f3->set('logger', $logger);
        }
        
        // setup database connection params
        // @see http://fatfreeframework.com/databases
        $db_enabled = !empty($f3->get('db.driver') || $f3->get('db.dsn'));
        if ($db_enabled) {
            if ($f3->get('db.driver') == 'sqlite') {
                $dsn = $f3->get('db.dsn');
                $dsn = substr($dsn, 0, strpos($dsn, '/')) . realpath('../') . substr($dsn, strpos($dsn, '/'));
                $db = new \DB\SQL($dsn);
                // attach any other sqlite databases - this example uses the full pathname to the db
                if ($f3->exists('db.sqlite.attached')) {
                    $attached = $f3->get('db.sqlite.attached');
                    $st = $db->prepare('ATTACH :filename AS :dbname');
                    foreach ($attached as $dbname => $filename) 
                        $st->execute(array(':filename' => $filename, ':dbname' => $dbname));
                }    
            } else {
                if (!$f3->get('db.dsn'))
                    $f3->set('db.dsn', sprintf("%s:host=%s;port=%d;dbname=%s",
                        $f3->get('db.driver'), 
                        $f3->get('db.hostname'), 
                        $f3->get('db.port'), 
                        $f3->get('db.name'))
                    );

                $db = new \DB\SQL(
                    $f3->get('db.dsn'), 
                    $f3->get('db.username'), 
                    $f3->get('db.password')
                );
            }
            \Registry::set('db', $db);
        }

        // setup outgoing email server for php mail command
        ini_set("SMTP", $f3->get('email.host'));
        ini_set('sendmail_from', $f3->get('email.from'));
        
        // @see http://fatfreeframework.com/optimization
        $f3->route('GET /minify/@type',
            function($f3, $args) {
                    $type = $args['type'];
                    $path = $f3->get('UI').$type.'/';
                    $files = str_replace('../','',$_GET['files']); // close potential hacking attempts  
                    echo \Web::instance()->minify($files, null, true, $path);
            },
            $f3->get('minify.ttl')
        );
        return $f3;
    }
    
    
    /**
    * final tasks for the application once run
    *
    * @return void
    */
    final public static function finish(&$f3)
    {
        // log script execution time if debugging
        $debug = $f3->get('debug');
        $logger = &$f3->ref('logger');
        
        if ($logger && $debug || $f3->get('application.environment') == 'development') {
            // log database transactions if level 3
            if ($debug == 3 && $f3->get('db')) 
                $logger->write(\Registry::get('db')->log());
            $execution_time = round(microtime(true) - $f3->get('TIME'), 3);
            $params = $f3->get('PARAMS');
            $logger->write('Script ' . $params[0] .' executed in ' . $execution_time . ' seconds using ' . 
                round(memory_get_usage() / 1024 / 1024, 2) . '/' . 
                round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB memory/peak');
        }
        return $f3;
    }
}
