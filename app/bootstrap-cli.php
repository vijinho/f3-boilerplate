<?php
/**
 * fat-free framework application initialisation for CLI mode
 * this is launched from bootstrap.php
 *
 * @package fatfree framework boilerplate
 * @author Vijay Mahrra <vijay.mahrra@gmail.com>
 * @copyright (c) Copyright 2013 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */

if (empty($f3)) {
    die("Run bootstrap.php to include this file cli.php!\n");
}

// setup routes
// @see http://fatfreeframework.com/routing-engine
// firstly load routes from ini file
$f3->config('config/routes-cli.ini');

// documentation route
$f3->route('GET /documentation/@page',function($f3, $params){
    $filename = 'doc/' . strtoupper($params['page']) . '.md';
    if (!file_exists($filename)) {
        die("Documentation Error!\n\nNo such document exists!\n");
    } else {
        echo $f3->read($filename);
    }
});

$f3->run();
