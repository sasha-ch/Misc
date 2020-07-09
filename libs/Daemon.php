<?php

/**
 *  Simple lib for start/stop/deamonize script
 *  Implements System_Daemon - like interface
 *
 *  Call statically
 *
 * Usage example:
 * $config = [
 * 'appName' => 'test',
 * 'logDir' => '...',
 * 'pidDir' => '...'
 * ];
 *
 * App::init($config);
 * App::daemon();
 * ...
 * App::stop();
 *
 * 
 *  ARGS supported:
 *  -nd == no-daemon == direct start in console == debug mode
 *  start|stop|restart == actions like in "/usr/bin/service"
 *
 *  -- DONE --
 *  Fork
 *  STDOUT/STDERR streams redirecting to log file
 *  PID files
 *
 *  -- TODO --
 *  signals
 *  children dispatch
 *  forever while()
 */
abstract class System
{

    protected static $appName;
    protected static $pidFile;
    protected static $logFile;

    protected static $initDone = false;
    protected static $isDying = false;
    protected static $inBackground = false;

    const LOG_WRITE_CHUNK_SIZE = 50;        //byte

    /**
     *  Initial setup of app. May be re-called
     *
     * @param array $config config of application: name, components, etc.
     *                      $appName        Name of our app "script"
     *                      $logDir         Dir to put log(s)
     *                      $pidDir         Dir to put .pid file
     *
     * @return void
     */
    public static function init($config)
    {
        $appName = $config['appName'] ? : pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_FILENAME);
        $logDir = $config['logDir'];
        $pidDir = $config['pidDir'] ? : sys_get_temp_dir();

        if (is_dir($logDir) && is_writable($logDir)) {
            static::$logFile = $logDir . "/" . $appName . ".log";
            static::getLogger()->setLogFile(static::$logFile);
        }

        static::$pidFile = $pidDir . "/" . $appName . ".pid";
        static::$appName = $appName;
        static::$initDone = true;
    }

    /**
     *  Get Logger object 
     * Example:
     * `return static::$container['logger'];`
     *
     * @return Logger
     */
    abstract public static function getLogger();

    /**
     * Daemonize script / process cli args for daemon
     *
     * @return bool
     */
    public static function daemon()
    {
        $argv = $GLOBALS['argv'];

        if (getopt('nd') || $argv[1] == '--no-daemon') {        //-nd == no-daemon == direct console start
            static::info("Start in no-daemon mode ok");
            return true;
        } elseif ($argv[1] == 'stop') {
            static::stop();
        } elseif ($argv[1] == 'restart') {
            static::restart();
        } else {
            $ok = static::start(true);
            if ($ok === false) {
                die("Error during ::start()");
            }
        }
    }

    /**
     *  Check init, check isRunning, set pid, fork, redirect IO streams to file
     *
     * @param bool $daemon if we need to start in daemon mode
     *
     * @return bool
     */
    public static function start($daemon = false)
    {
        if (!static::$initDone) {
            die("Call ::init() first");
        }

        if (static::isAppRunning()) {
            static::alert(static::$appName . ' is still running. Exiting');
            return false;
        }

        static::info('Starting ' . static::$appName . ', output in: ' . static::$logFile);

        if ($daemon == true) {
            //fork process, die parent
            static::fork();
            static::redirectIOStreams();
            static::$inBackground = true;
        }

        $pidOk = static::setPidFile();
        if (!$pidOk) {
            static::alert("Error during ::setPidFile()");
            return false;
        }

        return true;
    }

    /**
     *  Fork process
     *
     * @return bool
     */
    protected static function fork()
    {
        //fork process, die parent
        $childPid = pcntl_fork();
        if ($childPid == -1) {
            //fork failed
            die("could not fork\n");
        } elseif ($childPid) {
            //exit parent
            exit("exit parent process\n");
        }

        //set session leader
        $sid = posix_setsid();
        if ($sid == -1) {
            die('posix_setsid failed' . "\n");
        }

        return true;
    }

    /**
     * STD streams redirect
     *
     * @return bool
     */
    protected static function redirectIOStreams()
    {
        ini_set('error_log', static::$logFile);
        //don't work
        /*fclose(STDIN);
        fclose(STDOUT);
        fclose(STDERR);
        $STDIN = fopen('/dev/null', 'r');
        $STDOUT = fopen(static::$logFile, 'ab');
        $STDERR = fopen(static::$logFile, 'ab');*/

        $obFile = fopen(static::$logFile, 'a');
        if (!$obFile) {
            die("Log file " . static::$logFile . " is not writable.");
        }
        $obFileCallback = function ($buffer) use ($obFile){
            fwrite($obFile, $buffer);
            return '';
        };
        ob_start($obFileCallback, self::LOG_WRITE_CHUNK_SIZE);

        return true;
    }

    /**
     *  setPidFile
     *
     * @return bool
     */
    protected static function setPidFile()
    {
        $pid = posix_getpid();
        if (!file_put_contents(static::$pidFile, $pid)) {
            static::error('Unable to write pidfile: ' . static::$pidFile);
            return false;
        }
        return true;
    }

    /**
     * Stop script
     */
    public static function stop()
    {
        static::info('Stopping ' . static::$appName);
        static::shutdown(false, true);
    }

    /**
     * Restart script
     */
    public static function restart()
    {
        static::info('Restarting ' . static::$appName);
        static::shutdown(true, true);
    }

    /**
     * Shutdown or restart me or "me and all my brothers"
     *
     * @param bool $restart
     * @param bool $force == kill/restart foreign process
     */
    protected static function shutdown($restart = false, $force = false)
    {
        if (static::isDying()) {
            static::info('Already isDying');
            return null;
        }

        static::$isDying = true;

        if ((!static::isInBackground() && !$force) || (!file_exists(static::$pidFile) && $restart == false)) {
            static::info('Process was not daemonized yet, just halting current process');
            exit();
        }

        if (file_exists(static::$pidFile)) {
            $pid = file_get_contents(static::$pidFile);
            static::debug("Unlink " . static::$pidFile);
            unlink(static::$pidFile);
            if ($pid && (static::isRunning($pid) || $force) && $pid != getmypid()) {
                static::debug("Kill $pid process");
                @passthru('kill -9 ' . $pid);
            }
        }

        if ($restart) {
            static::debug("Starting new process");
            static::start(true);
        } else {
            static::debug("Exit process");
            exit();
        }
    }

    protected static function isDying()
    {
        return static::$isDying;
    }

    protected static function isInBackground()
    {
        return static::$inBackground;
    }

    /**
     * @return bool
     */
    protected static function isAppRunning()
    {
        $pidFile = static::$pidFile;
        if (!file_exists($pidFile)) {
            return false;
        }

        $pid = file_get_contents($pidFile);
        if (!$pid) {
            return false;
        }

        // Ping app
        if (!static::isRunning($pid)) {
            // Not responding so unlink pidfile
            unlink($pidFile);
            static::warning(
                'Orphaned pidfile found and removed: ' .
                $pidFile . '. Previous process crashed?'
            );
            return false;
        }

        return true;
    }

    /**
     *  If process with PID $pid is still running
     */
    protected static function isRunning($pid)
    {
        return posix_kill(intval($pid), 0);
    }

    /**
     * Protects your daemon by e.g. clearing statcache. Can optionally
     * be used as a replacement for sleep as well.
     *
     * @param integer $sleepSeconds Optionally put your daemon to rest for X s.
     *
     * @return void
     * @see start()
     * @see stop()
     */
    static public function iterate($sleepSeconds = 0)
    {
        if ($sleepSeconds >= 1) {
            sleep($sleepSeconds);
        } elseif (is_numeric($sleepSeconds) && $sleepSeconds > 0) {
            usleep($sleepSeconds * 1000000);
        }

        clearstatcache();

        // Garbage Collection (PHP >= 5.3)
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }

        return true;
    }

    /* Logger path */


    public static function alert()
    {
        $arguments = func_get_args();
        call_user_func_array([static::getLogger(), 'alert'], $arguments);
    }

    public static function error()
    {
        $arguments = func_get_args();
        call_user_func_array([static::getLogger(), 'error'], $arguments);
    }

    public static function warning()
    {
        $arguments = func_get_args();
        call_user_func_array([static::getLogger(), 'warning'], $arguments);
    }

    public static function info()
    {
        $arguments = func_get_args();
        call_user_func_array([static::getLogger(), 'info'], $arguments);
    }

    public static function debug()
    {
        $arguments = func_get_args();
        call_user_func_array([static::getLogger(), 'debug'], $arguments);
    }

}
