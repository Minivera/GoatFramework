<?php

namespace Core\Exceptions;

/**
 * Exception that is thrown when a set or a get try to access a non-existing 
 * property in a class. Only thrown if the class explicity doesn't allow dynamic 
 * properties.
 */
class InvalidPropertyException extends \Exception
{
    /**
     * Extended constructor for the exception. 
     * @param string $propertyName The property that lifted the exception.
     */
    public function __construct(string $propertyName) 
    {
        $message = "The property $propertyName doesn't exist in the current context ".
                "and the class doesn't allow dynamic properties.";
        parent::__construct($message);
    }
}
