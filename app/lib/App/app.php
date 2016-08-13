<?php

namespace App;

use FFMVC\Helpers;

/**
 * fat-free framework application
 * execute with call to \App\Run();
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2013-2016 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
function Run()
{
    // @see http://fatfreeframework.com/quick-reference#autoload
    $f3 = \Base::instance();
    \FFMVC\App::start();
    $f3->set('UNLOAD', function () {
        \FFMVC\App::finish();
    });

    // initialise application

    // single instances to registry

    // no logfile defined means no logging!
    $logfile = $f3->get('app.logfile');
    if (!empty($logfile)) {
        $logger = new \Log($logfile);
        \Registry::set('logger', $logger);
    }

    // set http-style dsn variables
    $params = $f3->get('db');
    if ($params) {
        $db = \FFMVC\Helpers\DB::instance()->newDb(array_key_exists('http_dsn', $params) ? $params['http_dsn'] : $params);
        \Registry::set('db', $db);
    }

    // is the url under /api ?
    $api = '/api' == substr($f3->get('PATH'), 0, 4);
    $f3->set('api', $api);

    // do not use sessions for api calls
    if (PHP_SAPI == 'cli' ||  $api) {
        if (session_status() !== PHP_SESSION_NONE) {
            session_write_close();
        }
    } elseif (session_status() == PHP_SESSION_NONE) {
        session_start();
        // this is an array so not in registry
        $f3->set('notifications', $f3->get('SESSION.notifications'));
        $f3->set('uuid', $f3->get('SESSION.uuid')); // logged-in user id

        // initialise gettext
        // override language from request
        $language = $f3->get('REQUEST.language');
        if (!empty($language)) {
            $f3->set('SESSION.language', $language);
        }

        // get language from session if set
        if (empty($language)) {
            $language = $f3->get('SESSION.language');
        }
    }

    // enable gettext if set
    if (!empty($f3->get('app.gettext'))) {
        // will now fall back to client browser language
        $language = empty($language) ? substr($f3->get('LANGUAGE'), 0, 2) : $language;
        // use LANG because f3 appends to LANGUAGE when setting
        $f3->set('LANG', $language);
        putenv('LANG=' . $language);
        setlocale(LC_ALL, $language);
        $domain = 'messages';
        bindtextdomain($domain, $f3->get('HOMEDIR') . '/app/i18n');
        bind_textdomain_codeset($domain, 'UTF-8');
        textdomain($domain);
    }

        // load cli routes and finish
    if (PHP_SAPI == 'cli') {
        $f3->route('GET /docs/@page', function ($f3, array $params) {
            $filename = '../docs/'.strtoupper($params['page']).'.md';
            if (!file_exists($filename)) {
                die("Documentation Error!\n\nNo such document exists!\n");
            } else {
                echo $f3->read($filename);
            }
        });

        // @see http://fatfreeframework.com/routing-engine
        //load routes from ini file
        $f3->config('config/routes-cli.ini');
        $f3->run();
        return;
    }

    // web start

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
        $logger = \Registry::get('logger');
        if (is_object($logger)) {
            $logger->write(print_r($f3->get('ERROR')));
        }

        // recursively clear existing output buffers:
        while (ob_get_level()) {
            ob_end_clean();
        }

        $debug = $f3->get('DEBUG');
        $api = !empty($f3->get('api'));
        $language = $f3->get('LANG');
        $e = $f3->get('ERROR');

        if (!$api && $e['code'] == '404') {
            $error_template = 'templates/' . $language . '/website/error/404.phtml';
            if (!file_exists($error_template)) {
                $error_template = 'templates/en/website/www/error/404.phtml';
            }
            include_once $error_template;
        } else {
            if (!$api) {
                $error_template = 'templates/' . $language . '/website/error/error.phtml';
                if (!file_exists($error_template)) {
                    $error_template = 'templates/en/website/error/error.phtml';
                }

                $debug_template = 'templates/' . $language . '/website/error/error.phtml';
                if (!file_exists($debug_template)) {
                    $debug_template = 'templates/en/website/error/debug.phtml';
                }

                include_once ('production' == $f3->get('app.env') && $debug < 1) ? $error_template
                            : $debug_template;
            } else {
                $response = Helpers\Response::instance();

                $data = [
                    'method' => $f3->get('VERB')
                ];

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
                $cleaned[strtolower($utf->trim($f3->clean($k)))] = $f3->recursive($v, function ($v) use ($f3, $utf) {
                    return $utf->trim($f3->clean($v));
                });
            }
            ksort($cleaned);
            $request = array_merge_recursive($request, $cleaned);
            $f3->set($var, $cleaned);
        }
    }

    unset($cleaned);

    // we don't want to include the session name in the request data
    $session_name = strtolower(session_name());
    if (array_key_exists($session_name, $request)) {
        unset($request[$session_name]);
    }

    ksort($request);
    $f3->set('REQUEST', $request);
    unset($request);

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

    // load /api/* routes and finish
    if (!empty($api)) {
        $f3->config('config/routes-api.ini');
        $f3->run();
        return;
    }

    // check csrf value if present, set input csrf to boolean true/false if matched session csrf
    if (!empty($f3->get('app.csrf_enabled'))) {
        $csrf = $f3->get('REQUEST.csrf');

        if (!$api && !empty($csrf)) {
            $f3->set('csrf', $csrf == $f3->get('SESSION.csrf'));
            $f3->clear('SESSION.csrf');
        }
    }

    $f3->route('GET /docs/@page', function ($f3, array $params) {

        $filename = '../docs/'.strtoupper($params['page']).'.md';

        if (!file_exists($filename)) {
            $html = '<h1>Documentation Error</h1><p>No such document exists!</p>';
            $f3->status(404);
        } else {
            $html = \Markdown::instance()->convert($f3->read($filename));
        }

        $f3->set('html', $html);
        echo \View::instance()->render('www/markdown-template.phtml');

    }, $f3->get('app.ttl_doc'));

    // load language-based routes, default english
    $f3->config('config/routes-en.ini');
    $file = 'config/routes-' . $language  . '.ini';
    if (file_exists($file)) {
        $f3->config($file);
    }

    $f3->run();
}
