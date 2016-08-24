<?php

namespace App;

/**
 * Setup Class.
 *
 * @author Vijay Mahrra <vijay@yoyo.org>
 * @copyright (c) Copyright 2016 Vijay Mahrra
 * @license GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
class Setup
{

    /**
     * setup database
     *
     * @param \Dice\Dice dependency injector
     * @param \Dice\Dice $dice
     * @return void
     */
    public static function database(&$dice)
    {
        $f3 = \Base::instance();
        $cache = \Cache::instance();
        // cli mode will not use cache on cli and will check db every time if in dev mode
        if ($f3->get('db.create') && (!$cache->exists('tables', $tables) || PHP_SAPI == 'cli' || 'dev' == $f3->get('app.env'))) {
            $db = $dice->create('DB\\SQL');
            $tables = $db->exec('SHOW TABLES');
            if (empty($tables)) {
                $sql = $f3->get('HOMEDIR') . '/data/db/sql/create.sql';
                $db->exec(file_get_contents($sql));
                $tables = $db->exec('SHOW TABLES');
            }
            $cache->set('tables', $tables, 600);
        }
    }
}
