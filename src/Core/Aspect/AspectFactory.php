<?php

namespace Core\Aspect;

/**
 * Factory to build an aspected object. The object must be extended from the 
 * Aspect Interface and implements the RegisterJoins function for the factory
 * to allow its construction.
 *
 * @author gst-pierre
 */
class AspectFactory
{
    //prevent class constructor. Static class.
    private function __construct()
    {
        
    }
    
    /**
     * Create the aspected class inside an aspect Manager if it is of the right type.
     * @param string $classname The name of the class to create, which extends
     * the Aspect abstract class.
     * @param array $parameters Parameters to pass to the class constructor when 
     * building it. Empty by default.
     * @return \Core\Aspect\AspectManager Return 
     * an aspect manager if it worked or null if it didn't.
     */
    static public function create(string $classname, array $parameters = array()) : \Core\Aspect\AspectManager
    {
        if(is_subclass_of($classname, __NAMESPACE__ . "\\" . "Aspect"))
        {
            return new AspectManager($classname, $parameters);
        }
        return null;
    }
}
