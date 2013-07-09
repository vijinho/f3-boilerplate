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

$f3 = require('../vendor/fatfree/lib/base.php');

// read config and overrides
// @see http://fatfreeframework.com/framework-variables#configuration-files
$f3->config('config/default.ini');
if (file_exists('config/config.ini'))
    $f3->config('config/config.ini');

// setup class autoloader
// @see http://fatfreeframework.com/quick-reference#autoload
$f3->set('AUTOLOAD', __dir__.';../vendor/fatfree/lib/;classes/;../vendor/');

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

// setup outgoing email server for php mail command
ini_set("SMTP", $f3->get('email.host'));
ini_set('sendmail_from', $f3->get('email.from'));

// command line does not have sessions so can't use session notifications
if (PHP_SAPI != 'cli') {
    // setup user notifications
    $f3->set('keep_notifications', false); // set to true somewhere to keep between requests
    $notifications = $f3->get('SESSION.notifications');
    if (!$f3->exists('SESSION.notifications')) {
        $f3->set('SESSION.notifications', array(
            'error' => array(),
            'warning' => array(),
            'success' => array(),
            'notice' => array()
        ));
    }
    // add messages like this with $f3->push('SESSION.notifications.error', 'ERROR MESSAGES');
}

// setup routes
// @see http://fatfreeframework.com/routing-engine
// firstly load routes from ini file
if (PHP_SAPI != 'cli') {
    $f3->config('config/routes.ini');

    // documentation route
    $f3->route('GET /doc/@page',function($f3, $params){
        $filename = 'doc/' . strtoupper($params['page']) . '.md';
        if (!file_exists($filename)) {
            die('No such documentation exists!');
        }
        echo \Markdown::instance()->convert($f3->read($filename));
    });

} else {
    $f3->config('config/routes-cli.ini');
}

$f3->run();

if (PHP_SAPI != 'cli') {
    // clear the session messages unless 'keep_notifications' is not false
    if ($f3->get('keep_notifications') === false) {
        $f3->set('SESSION.notifications', null);
    }
}

// log script execution time if debugging
if ($f3->get('DEBUG') || $f3->get('application.environment') == 'development') {
    $execution_time = microtime(true) - $f3->get('TIME');
    \Registry::get('logger')->write('Script executed in ' . $execution_time . ' seconds');
}
