<?php

namespace Core\Exceptions;

/**
 * Class that log exceptions in a text files named with the exception class type.
 * All critical exception will be logged thanks to the exception handler.
 */
class Logger
{
    /**
     * Defines the log file locations on the file system.
     */
    const LOGS_FOLDER = ".\." . DIRECTORY_SEPARATOR . "Logs". DIRECTORY_SEPARATOR;
    
    /**
     * Log a standard non-critical exception. Use when an exception is lifted, 
     * but the system can continue normal operations through the framework error handling.
     * @param \Exception $e The lifted exception.
     */
    public function logException(\Throwable $e)
    {
        date_default_timezone_set('UTC');
        $date = date('Y-m-d\TH:i:s');
        $classname = explode('\\', get_class($e));
        $filename = self::LOGS_FOLDER . array_pop($classname) . ".log";
        file_put_contents($filename, "[$date]" . $e->getMessage() . $e->getTraceAsString() . PHP_EOL, FILE_APPEND);
    }
    
    /**
     * Log a critical exception. Use when and uncaught exception is lifted and it 
     * can only be caught by the exception handler.
     * @param \Exception $e The lifted exception.
     */
    public function logCriticalException(\Throwable $e)
    {
        date_default_timezone_set('UTC');
        $date = date('Y-m-d\TH:i:s');
        $filename = self::LOGS_FOLDER . "Critical.log";
        file_put_contents($filename, "[$date]" . $e->getMessage() . $e->getTraceAsString() . PHP_EOL, FILE_APPEND);
    }
}
