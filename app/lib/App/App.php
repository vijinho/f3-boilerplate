<?php

namespace App;

use FFMVC\Helpers;

/**
 * fat-free framework application
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class App
{
    /**
     * Application-wide dependencies are injected
     *
     * @param \Log $logger
     * @param \DB\SQL $db
     */
    public function __construct(\Log $logger, \DB\SQL $db)
    {
        // single instances to registry
        \Registry::set('logger', $logger);
        \Registry::set('db', $db);
    }

    /**
     * The main application to run after environment is loaded
     */
    public function Main()
    {
        $f3 = \Base::instance();

        // is the url under /api ?
        $api = '/api' == substr($f3->get('PATH'), 0, 4);
        $f3->set('api', $api);
        $language = $f3->get('LANG');

        // do not use sessions for api calls
        if ($f3->get('CLI') ||  $api) {
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
        if ($f3->get('CLI')) {
            $f3->route('GET /docs/@page', function ($f3, array $params) {
                $filename = '../docs/' . strtoupper($params['page']) . '.md';
                if (!file_exists($filename)) {
                    echo "Documentation Error!\n\nNo such document exists!\n";
                    return;
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
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
        }

        // custom error handler if debugging
        $f3->set('ONERROR',
            function () use ($f3) {
            $logger = \Registry::get('logger');
            if (is_object($logger)) {
                $logger->write(print_r($f3->get('ERROR')), $f3->get('log.date'));
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
                    $error_template = 'templates/en/website/error/404.phtml';
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
                        'method' => $f3->get('VERB'),
                    ];

                    $data['error'] = [
                        'code' => substr($f3->snakecase(str_replace(' ', '',
                                    $e['status'])), 0),
                        'description' => $e['code'] . ' ' . $e['text'],
                    ];
                    if ($debug > 2) {
                        $data['error']['trace'] = $f3->trace(null, false);
                    }
                    $params = ['http_status' => $e['code']];
                    $response->json($data, $params);
                }
            }
            // http://php.net/manual/en/function.ob-end-flush.php
            while (@ob_end_flush());
        });

        // clean ALL incoming user input by default
        $request = [];
        $utf     = \UTF::instance();
        foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'COOKIE'] as $var) {
            $f3->copy($var, $var . '_UNCLEAN');
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
        $f3->copy('REQUEST', 'REQUEST_UNCLEAN');
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
                        'SERVER.PHP_AUTH_USER'  => $data[0],
                        'SERVER.PHP_AUTH_PW'    => $data[1],
                        'REQUEST.PHP_AUTH_USER' => $data[0],
                        'REQUEST.PHP_AUTH_PW'   => $data[1],
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
        if (!empty($f3->get('security.csrf'))) {
            $csrf = $f3->get('REQUEST.csrf');

            if (!$api && !empty($csrf)) {
                $f3->set('csrf', $csrf == $f3->get('SESSION.csrf'));
                $f3->clear('SESSION.csrf');
            }
        }

        $f3->route('GET /docs/@page', function ($f3, array $params) {

            $filename = '../docs/' . strtoupper($params['page']) . '.md';

            if (!file_exists($filename)) {
                $html = '<h1>Documentation Error</h1><p>No such document exists!</p>';
                $f3->status(404);
            } else {
                $html = \Markdown::instance()->convert($f3->read($filename));
            }

            $f3->set('html', $html);
            echo \View::instance()->render('/markdown-template.phtml');

        }, $f3->get('ttl.doc'));

        // @see http://fatfreeframework.com/optimization
        $f3->route('GET /minify/@type',
            function ($f3) {
                    $path = realpath(dirname(__FILE__) . '/../www/');
                    $files = str_replace('../', '', $f3->get('GET.files')); // close potential hacking attempts
                    echo \Web::instance()->minify($files, null, true, $path);
            },
            $f3->get('ttl.minify')
        );

        // load language-based routes, default english
        $f3->config('config/routes-en.ini');
        $file = 'config/routes-' . $language . '.ini';
        if (file_exists($file)) {
            $f3->config($file);
        }

        // from here we add-in routes generated from the database (cms routes)
        $f3->run();
    }
}
