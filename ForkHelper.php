<?php

namespace app\components\helpers;

use Yii;

/**
 * Class ForkHelper
 * Use for fork (cli) process
 */
class ForkHelper
{
    /**
     * Fork $count copies of process.
     * Exit parent process.
     * Reconnects to DB after fork
     */
    public static function fork($count)
    {
        for ($i = 0; $i < $count; $i++) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                //return false;
                die('could not fork');
            } else {
                if ($pid) {
                    // we are the parent
                    //pcntl_wait($status); //Protect against Zombie children
                } else {
                    // we are the child
                    static::dbReopen();
                    return true;
                }
            }
        }
        echo("Exit parent process");
        exit();
    }

    /**
     * Reopen DB connection
     */
    protected static function dbReopen()
    {
        $db = Yii::$app->db;
        $db->close();
        $db->open();
    }
}