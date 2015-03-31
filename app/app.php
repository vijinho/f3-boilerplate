<?php
namespace FFMVC\App;

/**
 * fat-free framework application 
 * execute with call to FFMVC\App\Run();
 * 
 * @package fatfree framework boilerplate
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

function Run() 
{
    // @see http://fatfreeframework.com/quick-reference#autoload
    $f3 = require_once('../vendor/bcosca/fatfree/lib/base.php');
    $f3->set('AUTOLOAD', __dir__.';../vendor/bcosca/fatfree/lib/;lib/;../vendor/');
    $app = new \FFMVC\App\Main();
    $f3 = $app->start($f3);
    $logger = &$f3->ref('logger');

        // cli start
    if (PHP_SAPI == 'cli') {
        $f3->route('GET /doc/@page',function($f3, $params){
            $filename = 'doc/' . strtoupper($params['page']) . '.md';
            if (!file_exists($filename))
                die("Documentation Error!\n\nNo such document exists!\n");
            else
                echo $f3->read($filename);
        });

        // @see http://fatfreeframework.com/routing-engine
        //load routes from ini file
        $f3->config('config/routes-cli.ini');
        
    } else { 
        // web start
         
        // custom error handler if debugging
        $debug = $f3->get('DEBUG');
        if (empty($debug)) {
            $f3->set('ONERROR',
                function() use($f3) {
                    header('Expires:  ' . \FFMVC\Helpers\Time::HTTP(time() + $f3->get('error.ttl')));
                    if ($f3->get('ERROR.code') == '404')
                        include_once 'ui/views/error/404.phtml';
                    else 
                        include_once 'ui/views/error/error.phtml';
                }
            );
        }
        
        $f3->route('GET /doc/@page',function($f3, $params){
            $filename = 'doc/' . strtoupper($params['page']) . '.md';
            echo \View::instance()->render('views/header.phtml');
            if (!file_exists($filename)) {
                echo '<h1>Documentation Error</h1><p>No such document exists!</p>';
                $f3->status(404);
            } else {
                echo \Markdown::instance()->convert($f3->read($filename));
            }
            echo \View::instance()->render('views/footer.phtml');
        }, $f3->get('doc.ttl'));

        $f3->config('config/routes.ini');
        
        // command line does not have SESSIONs so can't use SESSION notifications
        // setup user notifications
        // @see https://github.com/needim/noty for a library to present the messages
        $notifications = $f3->get('session.notifications');
        if (!$f3->exists('SESSION.notifications'))
            $f3->set('SESSION.notifications', array(
                'alert' => array(),
                'error' => array(),
                'warning' => array(),
                'success' => array(),
                'information' => array(),
                'confirmation' => array(),
            ));

        // add messages like this with $f3->push('SESSION.notifications.error', 'error messages');         
    }

    $f3->run();
    
    if (PHP_SAPI !== 'cli') {
        // clear the SESSION messages unless 'keep_notifications' is not false
        if ($f3->get('keep_notifications') === false) 
            $f3->set('SESSION.notifications', null);
    }
    
    $app->finish($f3);
}