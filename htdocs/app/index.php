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

$execution_time = microtime();

$f3 = require('../../include/fatfree/lib/base.php');

// read config and overrides
// @see http://fatfreeframework.com/framework-variables#configuration-files
$f3->config('config/default.ini');
if (file_exists('config/config.ini'))
    $f3->config('config/config.ini');

// setup class autoloader
// @see http://fatfreeframework.com/quick-reference#autoload
$f3->set('AUTOLOAD', __dir__.';../../include/fatfree/lib/;classes/helpers/;classes/models/;classes/controllers/;classes/');

// setup application logging
$f3->set('logger', new \Log('app.log'));

// setup database connection params
// @see http://fatfreeframework.com/databases
try {
    if (!$f3->get('db.dsn'))
        $f3->set('db.dsn', sprintf("%s:host=%s;port=%d;dbname=%s", $f3->get('db.driver'), $f3->get('db.hostname'), $f3->get('db.port'), $f3->get('db.name')));
    $f3->set('db.connection', new \DB\SQL($f3->get('db.dsn'), $f3->get('db.username'), $f3->get('db.password')));
} catch (Exception $e) {
    throw new Exception("Failed to initialise database!");
}

// setup routes
// @see http://fatfreeframework.com/routing-engine
$f3->route('GET /', 'controllers\Index->home');

$f3->run();

// log script execution time if debugging
if ($f3->get('DEBUG')) {
    $execution_time = microtime() - $execution_time;
    $f3->get('logger')->write('Script executed in ' . $execution_time . ' seconds');
}
