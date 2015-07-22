<?php

namespace FFMVC\App;

chdir(realpath(__DIR__ . '/../app'));
require_once 'lib/autoload.php'; // composer autoloader
// @see https://github.com/vijinho/f3-boilerplate
require_once 'app.php';
Run();
