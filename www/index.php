<?php

// f3-mvc application
// @see https://github.com/vijinho/f3-mvc
chdir(realpath(__DIR__ . '/../app'));
require_once 'app.php';
FFMVC\App\Run();
