<?php

namespace Core\Exceptions;

/**
 * Exception thrown when a class cannot be found by the system, similar to 
 * the fatal error thrown by PHP.
 */
class ClassNotFoundException extends \Exception
{
    /**
     * Class constructor
     * @param string $classname Name of the class that could not be found.
     */
    public function __construct(string $classname)
    {
        parent::__construct("The class $classname does not exist in the current "
                . "context, check if the file exists and if it is loaded.");
    }
}
