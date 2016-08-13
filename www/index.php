<?php
declare(strict_types=1);

namespace App;

if (session_status() == PHP_SESSION_NONE) {
    ini_set('session.auto_start', 'Off');
} else {
    ini_set('session.lazy_write', 'On');
}

// the app folder is where the app home and files are
chdir(__DIR__ . '/../app');
require_once '../lib/autoload.php'; // composer autoloader
require_once 'lib/App/app.php';
Run();
