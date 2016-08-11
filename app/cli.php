<?php

namespace FFMVC\App;

if (PHP_SAPI !== 'cli') {
    exit("This controller can only be executed in CLI mode.");
}

chdir(realpath(__DIR__));
require_once '../lib/autoload.php';
require_once 'app.php';
Run();
