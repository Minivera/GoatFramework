<?php

namespace Core\Aspect;

/**
 * Main manager for the aspect oriented part of the ORM. Its job is to allow 
 * registering of functions in classes as aspects and configuration of classes
 * to support aspect oriented programming. All is done at runtime.
 */
class AspectManager
{
    /**
    * Definition for the "before" join point, execute the advice before the 
    * method call
    */
   const JOIN_BEFORE = "Before";

   /**
    * Definition for the "after" join point, execute the advice after the
    * method call.
    */
   const JOIN_AFTER = "After";

   /**
    * Definition for the "around" join point, execute the code and uses the
    * original method where specified by the advice. The example shows how
    * a "around" advice should be written.
    * <pre><code>
    *     //code around the method
    *     $functionData = $GLOBALS["AOPCallAround"];
    *     $value = call_user_func(array($functionData[0], $functionData[1]), $functionData[2]);
    *     //code around the method
    * </code></pre>
    */
   const JOIN_AROUND = "Around";

   /**
    * Definition for the "throw" join point, execute the advice when an exception 
    * is thrown by the original method or any of the registered advices.
    */
   const JOIN_THROW = "Throw";

   /**
    * Definition for the "afterAnyway" join point, this advice runs at the end of the 
    * call, whether an exception was lifted or not. It does not run if the called 
    * method doesn't exist.
    */
   const JOIN_AFTER_ANYWAY = "AfterAnyway";
    
    /**
     * Instance of the managed class, it will allow the aspect manager to create
     * the managed class and apply the aspects on it's functions.
     * @var string Class name
     */
    private $managedClass = null;
    
    /**
     * Array containing all registered join points for the managed class formatted
     * as "JoinPoint;Pointcut" => "Advice". When calling an advice, this array will be
     * used to ensure all Join Points registered works.
     * <pre><code>
     *     $registeredJoin = array(
     *         "before;get*" => "\path\to\name\space\classname->logGetCall"
     *     );
     * </code></pre>
     * @var array Array of Join Points
     */
    private $registeredJoin = null;
    
    /**
     * Constructor of the class, when an aspected class is constructed, 
     * it fill fire and gets returned in place of the called class. 
     * @param string $classname Name of the class to manage
     * @param array $parameters Parameters to create the object. Empty by default.
     */
    public function __construct(string $classname, array $parameters = array())
    {
        $this->managedClass = new $classname(...$parameters);
        //Since the object MUST be a child of the Aspect abstract class, we run
        //the Join points registration
        $this->managedClass->registerJoinPoints($this);
    }
    
    /**
     * Magic method that will call the internal methods of the managed class,
     * it catches any methods that does not exists for the managed class, ensure
     * that the Join Points are executed and then returns the result.
     * @param string $name Name of the method to call
     * @param array $arguments Arguments to send to the called method
     * @return mixed Return the result of the called method.
     * @throws BadMethodCallException
     */
    public function __call(string $name, array $arguments)
    {
        //We check if the method exists in the first place, if not, throw a
        //BadMethodCallException
        if (!method_exists($this->managedClass, $name))
        {
            throw new BadMethodCallException();
        }
        $NewName = $this->getCompleteName($name);
        // Try cath to catch any exception from the Advices or the method.
        try
        {
            //Execute before advice(s)
            $this->locateJoinPoint($NewName, self::JOIN_BEFORE);
            //If an around JoinPoint exists, we run it with the method.
            $returnValue = $this->locateJoinPoint($NewName, self::JOIN_AROUND, $arguments);
            if ($returnValue === null)
            {
                $returnValue = call_user_func_array(array($this->managedClass, $name), $arguments);
            }
            //Execute after advice(s)
            $this->locateJoinPoint($NewName, self::JOIN_AFTER);
        }
        catch (\Exception $e)
        {
            //If a throw joinpoint exists, we run it.
            $returnValue = $e;
            $this->locateJoinPoint($NewName, self::JOIN_THROW, $e);
        }
        //run the afterAnyway JoinPoint if it exists
        $this->locateJoinPoint($NewName, self::JOIN_AFTER_ANYWAY);
        return $returnValue;
    }
    
    /**
     * Set the value of the managedClass if it exists.
     * @param string $name Name of the parameter to set.
     * @param mixed $value Value of the parameter to set.
     */
    public function __set(string $name, $value)
    {
        if (property_exists($this->managedClass, $name))
        {
            $this->managedClass->$name = $value;
        }
        else
        {
            throw new \Core\Exceptions\InvalidPropertyException($name);
        }
    }
    
    /**
     * Get the value of a specified parameter of the managed class.
     * @param string $name The name of the parameter to get.
     * @return mixed The value of the parameter.
     */
    public function __get(string $name)
    {
        if (property_exists($this->managedClass, $name))
        {
            return $this->managedClass->$name;
        }
        else
        {
            throw new \Core\Exceptions\InvalidPropertyException($name);
        }
    }
    
    /**
     * Function that get the fully qualified name of the current called function,
     * otherwise it would only be the simple name. Ex. \name\space\class->call() 
     * instead of "call".
     * @param string $name Name of the called function.
     * @return string Fully qualified name
     */
    private function getCompleteName(string $name)
    {
        if (!(isset($this) && get_class($this) == __CLASS__))
        {
            return get_class($this->managedClass) . "::$name()";
        }
        else
        {
            return get_class($this->managedClass) . "->$name()";
        }
    }
    
    /**
     * Locate the specified JoinPoint in the registered Join Points for the class,
     * it then execute the advice according to the specified JoinPoint
     * @param string $methodName Name of the method called in the __call for the 
     * aspected class, used to resolve the point cut and call the method in 
     * the "around" join point.
     * @param string $JoinPoint Type of join point to resolve.
     * @param mixed $Arguments Argument to pass to the advice who will treat it according
     * to the JoinPoint. See the Advice class.
     * @return mixed Either null or the result of the "around" join point.
     */
    private function locateJoinPoint($methodName, $JoinPoint, $Arguments = array())
    {
        if (empty($this->registeredJoin))
        {
            return null;
        }
        //For each registered Join points in the object.
        foreach ($this->registeredJoin as $key => $value)
        {
            //Cut the key in to to get the Join Point in the first cell and
            //the Point cut in the second.
            $Join = explode(";", $key);
            //Check if the current JoinPoint is the same as the one to locate.
            if (strcmp($Join[0], $JoinPoint) === 0)
            {
                //Create a new pointCut and resulve it.
                $PointCut = new PointCut($Join[1]);
                if ($PointCut->resolve($methodName))
                {
                    //If resolve, we can run the advice, create it and run it.
                    $advice = new Advice($JoinPoint, $value, $this->managedClass);
                    return $advice->execute($methodName, $Arguments);
                }
            }
        }
        return null;
    }
    
    /**
     * Register a before join point with the specified point cut and advice name.
     * @param string $pointcut Correctly formatted point cut to locate the method
     * @param string $advice Name of the function to call when this join point 
     * will trigger, must be fully qualified, the same way it will be called in
     * standard code.
     */
    public function registerBefore($pointcut, $advice)
    {
        $this->registeredJoin[self::JOIN_BEFORE . ";" . $pointcut] = $advice;
    }
    
    /**
     * Register an after join point with the specified point cut and advice name.
     * @param string $pointcut Correctly formatted point cut to locate the method
     * @param string $advice Name of the function to call when this join point 
     * will trigger, must be fully qualified, the same way it will be called in
     * standard code.
     */
    public function registerAfter($pointcut, $advice)
    {
        $this->registeredJoin[self::JOIN_AFTER . ";" . $pointcut] = $advice;
    }
    
    /**
     * Register an around join point with the specified point cut and advice name.
     * @param string $pointcut Correctly formatted point cut to locate the method
     * @param string $advice Name of the function to call when this join point 
     * will trigger, must be fully qualified, the same way it will be called in
     * standard code.
     */
    public function registerAround($pointcut, $advice)
    {
        $this->registeredJoin[self::JOIN_AROUND . ";" . $pointcut] = $advice;
    }
    
    /**
     * Register a throw join point with the specified point cut and advice name.
     * @param string $pointcut Correctly formatted point cut to locate the method
     * @param string $advice Name of the function to call when this join point 
     * will trigger, must be fully qualified, the same way it will be called in
     * standard code.
     */
    public function registerThrow($pointcut, $advice)
    {
        $this->registeredJoin[self::JOIN_THROW . ";" . $pointcut] = $advice;
    }
    
    /**
     * Register an after anyway join point with the specified point cut and advice name.
     * @param string $pointcut Correctly formatted point cut to locate the method
     * @param string $advice Name of the function to call when this join point 
     * will trigger, must be fully qualified, the same way it will be called in
     * standard code.
     */
    public function registerAfterAnyway($pointcut, $advice)
    {
        $this->registeredJoin[self::JOIN_AFTER_ANYWAY . ";" . $pointcut] = $advice;
    }
}