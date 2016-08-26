#!/usr/bin/php -q
<?php
declare (strict_types = 1);

namespace App;

require_once 'setup.php';

// initialise test database
try {
    $f3 = setup();
    if (!$f3->get('CLI')) {
        die('This can only be executed in CLI mode.');
    }
    $db = \Registry::get('db');
} catch (\Exception $e) {
    // fatal, can't continue
    throw($e);
}

$test = new \Test;

// insert tests here from f3
// https://fatfreeframework.com/test
// https://fatfreeframework.com/unit-testing

// This is where the tests begin
$test->expect(
    count($db->exec('SHOW TABLES')),
    'Tables exist?'
);

// Display the results; not MVC but let's keep it simple
foreach ($test->results() as $result) {
    echo $result['text'] . '<br>';
    if ($result['status']) {
        echo 'Pass';
    } else {
        echo 'Fail (' . $result['source'] . ')';
    }
    echo '<br>';
}
