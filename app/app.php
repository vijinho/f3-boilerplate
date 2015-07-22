<?php

namespace FFMVC\App;

use FFMVC\Helpers as Helpers;

/**
 * fat-free framework application
 * execute with call to FFMVC\App\Run();.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
function Run()
{
    // @see http://fatfreeframework.com/quick-reference#autoload
    $f3 = require_once 'lib/bcosca/fatfree-core/base.php';
    $f3->set('AUTOLOAD', __dir__.';bcosca/fatfree-core/;lib/');

    // initialise application
    Main::start($f3);

    // user feedback messages helper, inisialise so methods can be called statically
    $messages = Helpers\Messages::instance();
    $messages->init();

    // setup database connection params
    // @see http://fatfreeframework.com/databases
    if (!empty($f3->get('db.driver') || $f3->get('db.dsn') || $f3->get('db.http_dsn'))) {
        if ($http_dsn = $f3->get('db.http_dsn')) {
            if (preg_match('/^(?<driver>[^:]+):\/\/(?<username>[^:]+):(?<password>[^@]+)@(?<hostname>[^:]+):(?<port>[\d]+)?\/(?<database>.+)/', $http_dsn, $m)) {
                $f3->set('db.dsn', sprintf('%s:host=%s;port=%d;dbname=%s',
                    $m['driver'],
                    $m['hostname'],
                    $m['port'],
                    $m['database']
                ));
                $f3->mset(array(
                    'db.driver' => $m['driver'],
                    'db.hostname' => $m['hostname'],
                    'db.port' => $m['port'],
                    'db.name' => $m['database'],
                    'db.username' => $m['username'],
                    'db.password' => $m['password'],
                ));
            }
        } elseif (empty($f3->get('db.dsn'))) {
            $f3->set('db.dsn', sprintf('%s:host=%s;port=%d;dbname=%s',
                $f3->get('db.driver'),
                $f3->get('db.hostname'),
                $f3->get('db.port'),
                $f3->get('db.name'))
            );
        }

        if ($f3->get('db.driver') !== 'sqlite') {
            if ($dsn = $f3->get('db.dsn')) {
                $db = new \DB\SQL(
                    $dsn,
                    $f3->get('db.username'),
                    $f3->get('db.password')
                );
            }
        } else {
            $dsn = $f3->get('db.dsn');
            $dsn = substr($dsn, 0, strpos($dsn, '/')).realpath('../').substr($dsn, strpos($dsn, '/'));
            $db = new \DB\SQL($dsn);
            // attach any other sqlite databases - this example uses the full pathname to the db
            if ($f3->exists('db.sqlite.attached')) {
                $attached = $f3->get('db.sqlite.attached');
                $st = $db->prepare('ATTACH :filename AS :dbname');
                foreach ($attached as $dbname => $filename) {
                    $st->execute(array(':filename' => $filename, ':dbname' => $dbname));
                }
            }
        }

        \Registry::set('db', $db);
    }

        // cli start
    if (PHP_SAPI == 'cli') {
        $f3->route('GET /doc/@page', function ($f3, $params) {
            $filename = 'doc/'.strtoupper($params['page']).'.md';
            if (!file_exists($filename)) {
                die("Documentation Error!\n\nNo such document exists!\n");
            } else {
                echo $f3->read($filename);
            }
        });

        // @see http://fatfreeframework.com/routing-engine
        //load routes from ini file
        $f3->config('config/routes-cli.ini');
    } else {
        // web start

        // clean ALL incoming user input by default, lower-case input vars
        foreach (array('GET', 'POST') as $var) {
            $input = $f3->get($var);
            if (is_array($input) && count($input)) {
                $cleaned = array();
                foreach ($input as $k => $v) {
                    $k = strtolower(trim($f3->clean($k)));
                    $v = $f3->clean($v);
                    $cleaned[$k] = $v;
                }
                $f3->set($var, $cleaned);
            }
        }

        // custom error handler if debugging
        $debug = $f3->get('DEBUG');
        if (empty($debug)) {
            $f3->set('ONERROR',
                function () use ($f3) {
                    header('Expires:  '.\FFMVC\Helpers\Time::HTTP(time() + $f3->get('error.ttl')));
                    if ($f3->get('ERROR.code') == '404') {
                        include_once 'templates/www/error/404.phtml';
                    } else {
                        include_once 'templates/www/error/error.phtml';
                    }
                }
            );
        }

        // @see http://fatfreeframework.com/optimization
        $f3->route('GET /minify/@type',
            function ($f3, $args) {
                    $type = $args['type'];
                    $path = $f3->get('UI').$type.'/';
                    $files = str_replace('../', '', $_GET['files']); // close potential hacking attempts
                    echo \Web::instance()->minify($files, null, true, $path);
            },
            $f3->get('minify.ttl')
        );

        $f3->route('GET /doc/@page', function ($f3, $params) {
            $filename = 'doc/'.strtoupper($params['page']).'.md';
            echo \View::instance()->render('www/header.phtml');
            if (!file_exists($filename)) {
                echo '<h1>Documentation Error</h1><p>No such document exists!</p>';
                $f3->status(404);
            } else {
                echo \Markdown::instance()->convert($f3->read($filename));
            }
            echo \View::instance()->render('www/footer.phtml');
        }, $f3->get('doc.ttl'));

        $f3->config('config/routes.ini');
    }

    $f3->run();

    // terminate application
    Main::finish($f3);
}
