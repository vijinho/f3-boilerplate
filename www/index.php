<?php

chdir(realpath(__DIR__.'/../app'));
require_once 'lib/autoload.php'; // composer autoloader

// f3-boilerplate application
// @see https://github.com/vijinho/f3-boilerplate
require_once 'app.php';
FFMVC\App\Run();
