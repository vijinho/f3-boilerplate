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
    $f3 = require_once('lib/bcosca/fatfree/lib/base.php');
    $f3->set('AUTOLOAD', __dir__.';bcosca/fatfree/lib/;lib/');
    Main::start($f3); 

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
                        include_once 'templates/www/error/404.phtml';
                    else 
                        include_once 'templates/www/error/error.phtml';
                }
            );
        }
                
        $f3->route('GET /trade/',function($f3, $params){
            echo \View::instance()->render('www/header.phtml');
            print "poop"
            echo \View::instance()->render('www/footer.phtml');
        });
        
        $f3->route('GET /doc/@page',function($f3, $params){
            $filename = 'doc/' . strtoupper($params['page']) . '.md';
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
    
    Main::finish($f3);
}
