<?php

namespace Addons\Database;

/**
 * Allow the control of the internal ordering of functions call. It can, for example, 
 * ensure that a function called set is called before a function called get.
 */
trait OrderingChecker
{
    /**
     * First called function since the class creation. Does not add the 
     * constructor as the first called function.
     * @var string Name of the first function. 
     */
    protected $firstCalled;
    
    /**
     * Last called function in the class.
     * @var string Name of the function. 
     */
    protected $lastCalled;
    
    /**
     * Array of all the functions called in chronological order.
     * @var array Numerical array with the function as values. 
     */
    protected $order = array();
    
    /**
     * Register a called function in the order.
     * @param string $functioname Name of the called function.
     */
    protected function registerCall(string $functioname)
    {
        if (!isset($this->firstCalled))
        {
            $this->firstCalled = $functioname;
        }
        $this->lastCalled = $functioname;
        array_push($this->order, $functioname);
    }
    
    /**
     * Assert that the last called function is the same as one of the given function.
     * @param array $funcname Name of the functions to assert.
     * @return bool Result of the check.
     */
    protected function assertPrecedent(...$funcname) : bool
    {
        foreach ($funcname as $value)
        {
            if ($this->lastCalled === $value)
            {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Assert that the first called function is the same as one of the given functions.
     * @param array $funcname Name of the functions to assert.
     * @return bool Result of the check.
     */
    protected function assertFirst(...$funcname) : bool
    {
        foreach ($funcname as $value)
        {
            if ($this->firstCalled === $value)
            {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Assert that the given functions was called at least once in the order.
     * @param array $funcname Name of the functions to assert.
     * @return bool Result of the check.
     */
    protected function assertOrder(...$funcname) : bool
    {
        foreach ($funcname as $value)
        {
            if (in_array($value, $this->order))
            {
                return true;
            }
        }
        return false;
    }
}
