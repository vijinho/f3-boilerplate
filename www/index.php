<?php
declare (strict_types=1);

namespace App;

/**
 * Load, configure, run application
 */
function boot()
{
    if (session_status() == PHP_SESSION_NONE) {
        ini_set('session.auto_start', 'Off');
    } else {
        ini_set('session.lazy_write', 'On');
    }

    // the app folder is where the app home and files are
    chdir(__DIR__ . '/../app');
    require_once '../lib/autoload.php'; // composer autoloader

    // bootstrap initial environment
    $f3 = \Base::instance();
    \FFMVC\App::start();
    $f3->set('UNLOAD', function () {
        \FFMVC\App::finish();
    });

    // load dependency injection container
    $dice = new \Dice\Dice;

    // database connection used by app
    $dbConfig = $f3->get('db');
    $dice->addRule('DB\\SQL', ['shared' => true, 'constructParams' => [
        \FFMVC\Helpers\DB::createDbDsn($dbConfig),
        $dbConfig['user'],
        $dbConfig['pass'],
        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
    ]]);

    // logging for application
    $logfile = $f3->get('app.logfile');
    $dice->addRule('Log', ['shared' => true, 'constructParams' => [$logfile]]);

    // run the main application
    require_once 'lib/App/App.php';
    $app = $dice->create('App\\App');
    $app->Main();
}

// run the application
boot();
