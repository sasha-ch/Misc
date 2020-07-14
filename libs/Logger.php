<?php
/**
 *  Log functions 
 *
 *  Feature: we may pass to log* functions several args of any type
 */
class Logger
{
    
    const DEBUG = 100;
    const INFO = 200;
    const WARNING = 300;
    const ERROR = 400;
    const ALERT = 500;
    
    //TODO: set min loglevel in script
    protected $levels = [
        100 => 'DEBUG',
        200 => 'INFO',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'ALERT',
    ];
    
    public $logEcho = false;
    public $logFile = '';
    
    /**
     *  @return void
     */
    public function __construct()
    {
        $this->logEcho = (IS_CLI) ? 1 : 0;
    }
    
    /**
     *  Set file to log to
     *
     *  @param string $logFile
     */
    public function setLogFile($logFile)
    {
        $this->logFile = $logFile;
    }
    
    /**
     * log() shortcut with level
     * @param mixed
     */
    public function alert()
    {
        $arguments = func_get_args(); 
        $this->log(self::ALERT, $arguments);
    }

    /**
     * log() shortcut with level
     * @param mixed
     */
    public function error()
    {
        $arguments = func_get_args(); 
        $this->log(self::ERROR, $arguments);
    }

    /**
     * log() shortcut with level
     * @param mixed
     */
    public function warning()
    {
        $arguments = func_get_args(); 
        $this->log(self::WARNING, $arguments);
    }

    /**
     * log() shortcut with level
     * @param mixed
     */
    public function info()
    {
        $arguments = func_get_args(); 
        $this->log(self::INFO, $arguments);
    }

    /**
     * log() shortcut with level
     * @param mixed
     */
    public function debug()
    {
        $arguments = func_get_args(); 
        $this->log(self::DEBUG, $arguments);
    }

    /**
     * Internal logging function. Bridge between shortcuts like:
     * err(), warning(), info() and the actual addRecord() function
     *
     * @param mixed $level As string or constant
     * @param mixed $msg   Message
     *
     * @return boolean
     */
    protected function log($level, $arguments)
    {
        //$arguments = func_get_args();
        $dbgBt   = debug_backtrace();
        $history  = 2;
        
        //$level    = (string)$dbgBt[$history]['function'];
        $class    = (string)$dbgBt[($history+1)]['class'];
        $function = (string)$dbgBt[($history+1)]['function'];
        $file     = (string)$dbgBt[$history]['file'];
        $line     = (string)$dbgBt[$history]['line'];
        
        foreach($arguments as $msg){
            $this->addRecord($level, $msg, $file, $class, $function, $line);
        }
    }
    
    /**
     *  add Record to log file
     *  
     *  @param int $level    
     *  @param string $msg      
     *  @param string $file     
     *  @param string $class    
     *  @param string $function 
     *  @param string $line     
     *  @return string  
     */
    protected function addRecord($level, $msg, $file = false, $class = false, $function = false, $line = false)
    {
        $strDate  = '[' . date('Y-m-d H:i:s') . ']';
        $strLevel = str_pad($this->levels[$level], 8, ' ', STR_PAD_LEFT);
        if(empty($msg)){
            ob_start();
            var_dump($msg);
            $msg = ob_get_clean();
        }elseif(is_array($msg) || is_object($msg)){
            $msg = print_r($msg, true);
        }
        
        $logLine  = $strDate . ' ' . $strLevel . ': ' . $msg; // $str_ident
        
        if ($this->logEcho) {
            echo $logLine . "\n";
        }
        
        if($this->logFile){
            file_put_contents(
                $this->logFile,
                $logLine . "\n",
                FILE_APPEND
            );
        }
    }
}