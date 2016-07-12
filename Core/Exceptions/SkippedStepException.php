<?php

namespace Core\Exceptions;

/**
 * Exception lifted when a step has been skipped or forgotten in a class standard
 * procedure. for example, if a user forgot to use set before create in the 
 * dependency engine.
 */
class SkippedStepException extends \Exception
{
    public function __construct(string $step)
    {
        parent::__construct("A step has been skiped or forgotten, plese check if $step is correct");
    }
}
