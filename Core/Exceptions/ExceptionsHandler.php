<?php

namespace Core\Exceptions;

use Core\Engines\DependencyEngine as Container;

/**
 * This class acts as the last barrier for uncaught exceptions. It is tasked with
 * catching the exception, logging it as a critical exception and showing an 
 * error message to the user.
 */
class ExceptionsHandler
{
    /**
     * Class constructor
     */
    public function __construct() 
    {
        set_exception_handler(array($this, 'handleExceptions'));
    }

    /**
     * Allow the handling of critical uncaught exceptions. It logs the exception 
     * and return a blank state.
     * @param \Throwable $exception The uncaught exception.
     */
    public function handleExceptions(\Throwable $exception) 
    {
        $logger = Container::getInstance()->set("\Core\Exceptions\Logger")->create();
        $logger->logCriticalException($exception);
    } 
}
