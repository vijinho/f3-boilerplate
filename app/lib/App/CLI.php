<?php

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

    chdir(realpath(__DIR__ . '/../../'));
    require_once '../lib/autoload.php';

    // bootstrap initial environment
    $f3 = \Base::instance();

    if (empty($f3->get('CLI'))) {
        die('This can only be executed in CLI mode.');
    }

    \FFMVC\App::start();
    $f3->set('UNLOAD', function () {
        \FFMVC\App::finish();
    });

    // load dependency injection container
    $dice = new \Dice\Dice;

    // logging for application
    $logfile = $f3->get('log.file');
    $dice->addRule('Log', ['shared' => true, 'constructParams' => [$logfile]]);

    // database connection used by app
    $dbConfig = $f3->get('db');
    $dice->addRule('DB\\SQL', ['shared' => true, 'constructParams' => [
        \FFMVC\Helpers\DB::createDbDsn($dbConfig),
        $dbConfig['user'],
        $dbConfig['pass'],
        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION],
    ]]);

    // auto-create database if options set
    \App\Setup::database($dice);

    // run the main application
    require_once 'lib/App/App.php';
    $app = $dice->create('App\\App');
    $app->Main();
}

// run the application
boot();
