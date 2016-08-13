<?php

namespace App;

if (PHP_SAPI !== 'cli') {
    die("This can only be executed in CLI mode.");
}

// run from app main folder
chdir(realpath(__DIR__ . '/../../'));
require_once '../lib/autoload.php';
require_once 'lib/App/app.php';
Run();
