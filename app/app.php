<?php

namespace FFMVC\App;

use FFMVC\Helpers as Helpers;
use FFMVC\Models as Models;


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

        // default cacheable data time in seconds from config
    $ttl = $f3->get('app.ttl');

    // setup database connection params
    // @see http://fatfreeframework.com/databases
    $db = null;

    // set http-style dsn variables
    $httpDSN = $f3->get('db.http_dsn');
    if (!empty($httpDSN)) {
        $m = parse_url($httpDSN);

        $m['path'] = substr($m['path'], 1);
        $m['port'] = empty($m['port']) ? 3306 : (int) $m['port'];

        $dsn = sprintf('%s:host=%s;port=%d;dbname=%s',
            $m['scheme'], $m['host'], $m['port'], $m['path']
        );
        $f3->set('db.dsn', $dsn, $ttl);

        $f3->mset([
            'db.driver' => $m['scheme'],
            'db.host'   => $m['host'],
            'db.port'   => $m['port'],
            'db.name'   => $m['path'],
            'db.user'   => $m['user'],
            'db.pass'   => $m['pass'],
        ]);
    }

    // if dsn is still not set, set the dsn from hive vars
    $dsn = $f3->get('db.dsn');
    if (empty($dsn)) {
        $dsn = sprintf('%s:host=%s;port=%d;dbname=%s',
            $f3->get('db.driver'), $f3->get('db.host'), $f3->get('db.port'), $f3->get('db.name')
        );
    }

    // finally if we have enough settings instantiate db
    $driver = $f3->get('db.driver');
    if (!empty($driver) && !empty($dsn)) {
        $db = new \DB\SQL(
            $dsn,
            $f3->get('db.user'),
            $f3->get('db.pass'),
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
        \Registry::set('db', $db, $ttl);
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

        // is the url under /api ?
        $api = '/api' == substr($f3->get('PATH'), 0, 4);
        $f3->set('api', $api);

        // do not use sessions for api calls
        if ($api && session_status() !== PHP_SESSION_NONE) {
            session_write_close();
        } else if (session_status() == PHP_SESSION_NONE) {
            session_start();
            $f3->set('notifications', $f3->get('SESSION.notifications'));
            $f3->set('uuid', $f3->get('SESSION.uuid'));
        }

        // user feedback messages helper, inisialise so methods can be called statically
        $notifications = Helpers\Notifications::instance();
        $notifications->init();

        // Use https://github.com/filp/whoops if debug level is 4
        $debug = $f3->get('DEBUG');

        if (!$api && $debug == 4) {
            $w = new \Whoops\Run;
            $w->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $w->register();
        }

        // custom error handler if debugging
        $f3->set('ONERROR',
            function () use ($f3) {

            // recursively clear existing output buffers:
            while (ob_get_level()) {
                ob_end_clean();
            }

            $api = !empty($f3->get('api'));

            if (!$api && $f3->get('ERROR.code') == '404') {
                include_once 'templates/www/error/404.phtml';
            } else {

                $debug = $f3->get('DEBUG');

                if (!$api) {
                    include_once ($debug < 1 || 'production' == $f3->get('app.env')) ? 'templates/www/error/error.phtml'
                                : 'templates/www/error/debug.phtml';
                } else {
                    $response = Helpers\Response::instance();

                    $data = [
                        'service' => 'API',
                        'version' => 1,
                        'time' => time(),
                        'method' => $f3->get('VERB')
                    ];

                    $e = $f3->get('ERROR');

                    $data['error'] = [
                        'code' => substr($f3->snakecase(str_replace(' ', '',
                                    $e['status'])), 0),
                        'description' => $e['code'] . ' ' . $e['text']
                    ];

                    if ($debug == 3) {
                        // show the $e['trace'] but it's in HTML!
                    }

                    $params = ['http_status' => $e['code']];
                    $return = $f3->get('REQUEST.return');

                    switch ($return) {
                        default:
                        case 'json':
                            $response->json($data, $params);
                    }
                }
            }
            // http://php.net/manual/en/function.ob-end-flush.php
            @ob_end_flush();
        });

        // clean ALL incoming user input by default
        $request = [];
        $utf = \UTF::instance();
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'COOKIE'] as $var) {
            $input = $f3->get($var);
            if (is_array($input) && count($input)) {
                $cleaned = [];

                foreach ($input as $k => $v) {
                    $k = strtolower($utf->trim($f3->clean($k)));
                    $v = $f3->clean($v);
                    if (empty($v)) {
                        continue;
                    }
                    $cleaned[$k] = $v;
                    $request[$k] = $v;
                }

                ksort($cleaned);
                $f3->set($var, $cleaned);
            }
        }

        unset($cleaned);
        ksort($request);
        // we don't want to include the session name in the request data
        $session_name = strtolower(session_name());
        if (array_key_exists($session_name, $request)) {
            unset($request[$session_name]);
        }
        $f3->set('REQUEST', $request);
        unset($request);

        // check csrf value if present, set input csrf to boolean true/false if matched session csrf
        if (!empty($f3->get('app.csrf_enabled'))) {

            $csrf = $f3->get('REQUEST.csrf');

            if (!$api && !empty($csrf)) {
                $f3->set('csrf', $csrf == $f3->get('SESSION.csrf'));
                $f3->clear('SESSION.csrf');
            }
        }

        // get the access token and basic auth and set it in REQUEST.access_token
        $token = $f3->get('REQUEST.access_token');
        foreach ($f3->get('SERVER') as $k => $header) {

            if (stristr($k, 'authorization') !== false) {

                if (preg_match('/Bearer\s+(?P<access_token>.+)$/i', $header, $matches)) {

                    $token = $matches['access_token'];

                } elseif (preg_match('/Basic\s+(?P<data>.+)$/i', $header, $matches)) {

                    $data = preg_split('/:/', base64_decode($matches['data']));

                    $f3->mset([
                        'SERVER.PHP_AUTH_USER' => $data[0],
                        'SERVER.PHP_AUTH_PW' => $data[1],
                        'REQUEST.PHP_AUTH_USER' => $data[0],
                        'REQUEST.PHP_AUTH_PW' => $data[1]
                    ]);
                }
            }
        }
        if (!empty($token)) {
            $f3->set('REQUEST.access_token', $token);
        }

        // @see http://fatfreeframework.com/optimization
        $f3->route('GET /minify/@type',
            function ($f3, $args) {
                $type = $args['type'];
                $path = realpath(dirname(__FILE__) . '/../www/');
                $files = str_replace('../', '', $_GET['files']); // close potential hacking attempts
                echo \Web::instance()->minify($files, null, true, $path);
            },
            $f3->get('app.ttl_minify')
        );

        $f3->route('GET /doc/@page', function ($f3, $params) {

            $filename = 'doc/'.strtoupper($params['page']).'.md';

            if (!file_exists($filename)) {
                $html = '<h1>Documentation Error</h1><p>No such document exists!</p>';
                $f3->status(404);
            } else {
                $html = \Markdown::instance()->convert($f3->read($filename));
            }

            $f3->set('html', $html);
            echo \View::instance()->render('www/markdown-template.phtml');

        }, $f3->get('app.ttl_doc'));

        $f3->config('config/routes.ini');
    }

    $f3->run();

    // terminate application
    Main::finish($f3);
}
