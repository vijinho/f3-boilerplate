<?php
namespace App;

/**
 * Load, configure, test environment, return database handle to it
 *
 * @return \Base $f3 instance
 */
function &setup()
{
    if (session_status() == PHP_SESSION_NONE) {
        ini_set('session.auto_start', 'Off');
    } else {
        ini_set('session.lazy_write', 'On');
    }

    chdir(__DIR__ . '/../app');
    require_once '../lib/autoload.php'; // composer autoloader

    // bootstrap initial environment
    $f3 = \Base::instance();

    \FFMVC\App::start();

    // override db params for test
    // setup database connection params
    // @see http://fatfreeframework.com/databases
    $httpDSN = $f3->get('db.dsn_test');
    if (!empty($httpDSN)) {
        $dbParams = $f3->get('db');
        $params = \FFMVC\Helpers\DB::instance()->parseHttpDsn($httpDSN);
        $params['dsn'] = \FFMVC\Helpers\DB::instance()->createDbDsn($params);
        $dbParams = array_merge($dbParams, $params);
        $f3->set('db', $dbParams);
    }

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
        [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
    ]]);

    // auto-create database if options set
    try {
        $db = $dice->create('DB\\SQL');
        \Registry::set('db', $db);
    } catch (\PDOException $e) {
        switch ($e->getCode()) {
            case 1049: // db doesn't exist
                die($e->getMessage());
            break;
            default:
                throw($e);
                return;
        }
    }

    // drop existing tables, re-create db
    try {
        $results = $db->exec('SHOW TABLES');
        $queries = [];
        if (!empty($results)) {
            $queries[] = 'SET FOREIGN_KEY_CHECKS = 0';
            foreach ($results as $result) {
                foreach ($result as $table) {
                    $queries[] = sprintf('DROP TABLE IF EXISTS %s', $db->quotekey($table));
                }
            }
            $queries[] = 'SET FOREIGN_KEY_CHECKS = 1';
            $db->exec($queries);
        }
        \App\Setup::database($dice);
        \Registry::set('db', $db);
    } catch (\PDOException $e) {
        throw($e);
    }

    return $f3;
}
