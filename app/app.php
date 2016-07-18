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

    // setup database connection params
    // @see http://fatfreeframework.com/databases
    $db = null;
    $driver = $f3->get('db.driver');
    $dsn = $f3->get('db.dsn');
    $http_dsn = $f3->get('db.http_dsn');
    if (!empty($driver) || $dsn || $http_dsn) {
        if ($http_dsn = $f3->get('db.http_dsn')) {
            $m = parse_url($http_dsn);
            $m['path'] = substr($m['path'], 1);
            $m['port'] = empty($m['port']) ? 3306 : $m['port'];
            $f3->set('db.dsn', sprintf('%s:host=%s;port=%d;dbname=%s',
                $m['scheme'],
                $m['host'],
                $m['port'],
                $m['path']
            ));
            $f3->mset(array(
                'db.driver' => $m['scheme'],
                'db.hostname' => $m['host'],
                'db.port' => $m['port'],
                'db.name' => $m['path'],
                'db.username' => $m['user'],
                'db.password' => $m['pass'],
            ));
        } elseif (empty($dsn)) {
            $f3->set('db.dsn', sprintf('%s:host=%s;port=%d;dbname=%s',
                $f3->get('db.driver'),
                $f3->get('db.hostname'),
                $f3->get('db.port'),
                $f3->get('db.name')
            ));
        }

        $driver = $f3->get('db.driver');
        $dsn = $f3->get('db.dsn');
        if (!empty($dsn)) {
            $db = new \DB\SQL(
                $dsn,
                $f3->get('db.username'),
                $f3->get('db.password'),
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
        }
    }
    \Registry::set('db', $db);

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
            $f3->set('messages', $f3->get('SESSION.messages'));
        }

        // user feedback messages helper, inisialise so methods can be called statically
        $messages = Helpers\Messages::instance();
        $messages->init();

        // custom error handler if debugging
        $f3->set('ONERROR',
            function () use ($f3) {
                    // recursively clear existing output buffers:
                while (ob_get_level()) {
                    ob_end_clean();
                }
                if ($f3->get('ERROR.code') == '404' && stristr($f3->get('PATH'), '/api') == false) {
                    include_once 'templates/www/error/404.phtml';
                } else {
                    $debug = $f3->get('DEBUG');
                    if ($api) {
                        $response = Helpers\Response::instance();
                        $data = array(
                            'service' => 'API',
                            'version' => 1,
                            'time' => time(),
                            'method' => $f3->get('VERB')
                        );
                        $e = $f3->get('ERROR');
                        $data['error'] = array(
                            'code' => substr($f3->snakecase(str_replace(' ', '', $e['status'])), 0),
                            'description' => $e['code'] . ' ' . $e['text']
                        );
                        if ($debug == 3) {
                            // show the $e['trace'] but it's in HTML!
                        }
                        $params = array('http_status' => $e['code']);
                        $return = $f3->get('REQUEST.return');
                        switch ($return) {
                            case 'xml':
                                $response->xml($data, $params);
                                break;

                            default:
                                case 'json':
                                $response->json($data, $params);
                        }
                    } else {
                        include_once $debug < 3 ? 'templates/www/error/error.phtml' :  'templates/www/error/debug.phtml';
                    }
                }
                // http://php.net/manual/en/function.ob-end-flush.php
                ob_end_flush();
        });

        // clean ALL incoming user input by default
        $request = array();
        foreach (array('GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'COOKIE') as $var) {
            $input = $f3->get($var);
            if (is_array($input) && count($input)) {
                $cleaned = array();
                foreach ($input as $k => $v) {
                    $k = strtolower(trim($f3->clean($k)));
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
        ksort($request);
        $f3->set('REQUEST', $request);
        $f3->set('PageHash', md5(json_encode(array_merge($request, $_SERVER, $_ENV))));
        unset($cleaned);
        unset($request);

        // get the access token and basic auth and set it in REQUEST.access_token
        foreach ($f3->get('SERVER') as $k => $header) {
            if (stristr($k, 'authorization') !== false) {
                if (preg_match('/Bearer\s+(?P<access_token>.+)$/i', $header, $matches)) {
                    $token = $matches['access_token'];
                } elseif (preg_match('/Basic\s+(?P<data>.+)$/i', $header, $matches)) {
                    $data = preg_split('/:/', base64_decode($matches['data']));
                    $f3->mset(array(
                        'SERVER.PHP_AUTH_USER' => $data[0],
                        'SERVER.PHP_AUTH_PW' => $data[1],
                        'REQUEST.PHP_AUTH_USER' => $data[0],
                        'REQUEST.PHP_AUTH_PW' => $data[1]
                    ));
                }
            }
        }
        if (empty($token)) {
            $token = $f3->get('REQUEST.access_token');
        }
        if (!empty($token)) {
            // check there is a valid user id for the token
            if (preg_match('/^(?P<user_id>.+)\-.+$/i', $token, $matches)) {
                if (!empty($matches['user_id'])) {
                    $f3->mset(array(
                        'REQUEST.access_token' => $token,
                        'REQUEST.user_id' => $matches['user_id'],
                    ));
                }
            } else {
                // developer access token
                $f3->set('REQUEST.access_token', $token);
            }
        }

        // @see http://fatfreeframework.com/optimization
        $f3->route('GET /minify/@type',
            function ($f3, $args) {
                    $type = $args['type'];
                    $path = realpath(dirname(__FILE__) . '/../www/');
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


        // global validator
        $validator = new Helpers\Validator();
        \Registry::set('validator', $validator);

        $f3->config('config/routes.ini');
    }

    $f3->run();

    // terminate application
    Main::finish($f3);
}
