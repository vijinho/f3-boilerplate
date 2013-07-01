<?php
namespace application;

/**
 * fat-free framework application initialisation
 *
 * @package fatfree framework boilerplate
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

chdir('../app'); // change to app folder outside of webroot
$execution_time = microtime();

$f3 = require('../include/fatfree/lib/base.php');

// read config and overrides
// @see http://fatfreeframework.com/framework-variables#configuration-files
$f3->config('config/default.ini');
if (file_exists('config/config.ini'))
    $f3->config('config/config.ini');

// setup class autoloader
// @see http://fatfreeframework.com/quick-reference#autoload
$f3->set('AUTOLOAD', __dir__.';../include/fatfree/lib/;classes/helpers/;classes/models/;classes/controllers/;classes/');

// setup application logging
\Registry::set('logger', new \Log($f3->get('application.logfile')));

// setup database connection params
// @see http://fatfreeframework.com/databases
if (!$f3->get('db.dsn')) {
    $f3->set('db.dsn', sprintf("%s:host=%s;port=%d;dbname=%s",
        $f3->get('db.driver'), $f3->get('db.hostname'), $f3->get('db.port'), $f3->get('db.name'))
    );
}
\Registry::set('db', new \DB\SQL($f3->get('db.dsn'), $f3->get('db.username'), $f3->get('db.password')));

// setup routes
// @see http://fatfreeframework.com/routing-engine
// firstly load routes from ini file
$f3->config('config/routes.ini');

$f3->run();

// log script execution time if debugging
if ($f3->get('DEBUG') || $f3->get('application.environment') == 'development') {
    $execution_time = microtime() - $execution_time;
    \Registry::get('logger')->write('Script executed in ' . $execution_time . ' seconds');
}
