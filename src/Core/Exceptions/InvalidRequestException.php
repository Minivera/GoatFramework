<?php

namespace Core\Exceptions;

/**
 * An exception made to inform the programmer that his request is invalid. 
 */
class InvalidRequestException extends \Exception
{
    /**
     * Class constructor.
     * @param string $InvalidClassName The class name that caused the exception.
     */
    public function __construct(string $InvalidClassName) 
    {
        $message = "The request could not be processed, the $InvalidClassName "
                . "class does not exist. A complete request must have at least a "
                . "valid View and Model class. This error is the equivalent of " 
                .  "a 404 HTML error.";
        parent::__construct($message);
    }
}
