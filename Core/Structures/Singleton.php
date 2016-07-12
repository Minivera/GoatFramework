<?php

namespace Core\Structures;

/**
 * Singleton class used to identify a class as a singleton in the 
 * dependency Engine.
 */
class Singleton
{
    /**
     * Instance of the singleton class. It is global to the application, so the 
     * whole application always use the same configured instance.
     * @var Singleton Instance.
     */
    protected static $instance;
    
    /**
     * Static method used to obtain the singleton instance of this class.
     * @return Singleton Singleton instance.
     */
    public static function getInstance()
    {
        if (null === static::$instance) 
        {
            static::$instance = new static();
        }
        return static::$instance;
    }
    
     /**
     * Protected constructor to prevent creating a new instance of the
     * class via the new operator from outside of this class.
     */
    protected function __construct()
    {
    }

    /**
     * Private clone method to prevent cloning of the instance of the
     * class instance.
     * @return void
     */
    private function __clone()
    {
    }

    /**
     * Private unserialize method to prevent unserializing of the class
     * instance.
     * @return void
     */
    private function __wakeup()
    {
    }
}
