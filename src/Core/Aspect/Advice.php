<?php

namespace Core\Aspect;

/**
 * Class that defines and manage the advice part of AOP, an advice is a function
 * that is executed according to its JoinPoint. The behavior of the advice changes
 * according to it JoinPoint. An advice call in the register must be correctly 
 * formated to work, be fully qualified and contains no parenthesis.
 * 
 * <ul>
 * <li>Before : The function is run as usual, the potential result of that function
 *      is added to the globals["AOPVarBefore"] variable.</li>
 * <li>After : The function is run as usual, the potential result of that function
 *      is added to the globals["AOPVarAfter"] variable.</li>
 * <li>Around : The function is run with the original function called by the aspected
 *      object in the globals["AOPCallAround"] variable to be called by the advice.
 *      The result of the function is put into the globals["AOPVarAround"] variable.</li>
 * <li>Throw : The function is run as usual with the exception message as an 
 *      argument to the constructor, the potential result of that function
 *      is added to the globals["AOPVarThrow"] variable.</li>
 * <li>AfterAnyway : The function is run as usual, the potential result of that function
 *      is added to the globals["AOPVarAfterAnyway"] variable.</li>
 * </ul>
 *
 * Globals are used to ensure the continuity and sharing of variables, all are 
 * unset once the aspect run cycle has ended. If no data is returned by an advice,
 * the globals can be used to store a variable used later. For example, a timer
 * can be stored in the before global and used in the after.
 * 
 * Finally, there is four possible call for an advice :
 * <ul>
 *      <li>A function in a file (Name\Space\func()), it is called normally if it exists.</li>
 *      <li>A static function of a class (Name\Space\class::func()), it is called 
 *      normally if it exists.</li>
 *      <li>An object function (Name\Space\class->func()), a new instance of that 
 *      object is created before the call.</li>
 *      <li>A local function ($this->func() or $this::func()), the current managed 
 *      object is used in this case, the method contained in that object will be used.</li>
 * </ul>
 * 
 */
class Advice
{
    /**
    * Definition for the global containing the result of the "before" advice.
    */
    const GLOBALS_VAR_BEFORE = "AOPVarBefore";
    
    /**
     * Definition of the global containing the result of the "after" advice.
     */
    const GLOBALS_VAR_AFTER = "AOPVarAfter";

    /**
     * Definition of the global containing the method call in the "around" advice, it
     * is used to create a function name and call it in the advice method.
     * <pre><code>
     *      $functionData = $GLOBALS["AOPCallAround"];
     *      return call_user_func_array(array($functionData[0], $functionData[1]), $functionData[2]);
     * </code></pre>
     */
    const GLOBALS_CALL_AROUND = "AOPCallAround";

    /**
     * Definition of the global containing the result of the "around" advice.
     */
    const GLOBALS_VAR_AROUND = "AOPVarAround";

    /**
     * Definition of the global containing the result of the "Throw" advice.
     */
    const GLOBALS_VAR_THROW = "AOPVarThrow";

    /**
     * Definition of the global containing the result of the "afterAnyway" advice.
     */
    const GLOBALS_VAR_ANYWAY = "AOPVarAfterAnyway";
    
    /**
     * The JoinPoint of this advice, the correct method is called with this
     * join.
     * @var sring 
     */
    private $JoinPoint = null;
    
    /**
     * The name of the method the advice will run, it can contains call to 
     * global variables or return a value that will be stored in the corresponding 
     * global.
     * 
     * @var string 
     */
    private $AdviceName = null;
    
    /**
     * The class constructor for the advice, it initializes the class for the 
     * following execute function with the right parameters.
     * @param string $JoinPoint The JoinPoint associated with the advice.
     * @param string $methodName The name of the function associated with the 
     * advice.
     * @param \Core\Aspect\Aspect $managedObject Instance of the aspect managed object.
     * method, it is only used in some JoinPoint.
     */
    public function __construct(string $JoinPoint, string $methodName, \Core\Aspect\Aspect $managedObject)
    {
        $this->JoinPoint = $JoinPoint;
        //check the two cases that must be worked around
        //is the advice from the current managed objet?
        if (strpos($methodName, '$this') !== false)
        {
            $this->AdviceName = array($managedObject, substr($methodName, 7));
        }
        //Is the advice from an object that must be created?
        else if(strpos($methodName, '->') !== false)
        {
            $classname = substr($methodName, 0, strrpos($methodName, "->"));
            $this->AdviceName = array(new $classname(), substr(strrchr($methodName, '->'), 2));
        }
        else
        {
            $this->AdviceName = $methodName;
        }
    }
    
    /**
     * Standard execute function, it creates the right execute Join function name
     * and run this function.
     * @param string $methodName The name of the original method called in the
     * __call() method.
     * @param mixed $Arguments The potential argument passed to the __call()
     * @return mixed The return value of the advice, depends on the advice function.
     */
    public function execute(string $methodName, $Arguments = array())
    {
        $funcName = "executeJoin$this->JoinPoint";
        return $this->$funcName($methodName, $Arguments);
    }
    
    /**
     * Execute the before Join and store the result in the corresponding globals
     * @param string $methodName Name of the original method in the __call() function
     * of the aspected object, it is ignored.
     * @param mixed $Arguments The arguments of the original __call() function, 
     * it is ignored.
     */
    private function executeJoinBefore(string $methodName, $Arguments = array())
    {
        if (is_array($Arguments))
        {
            $GLOBALS[self::GLOBALS_VAR_BEFORE] = call_user_func_array($this->AdviceName, $Arguments);
        }
        else
        {
            $GLOBALS[self::GLOBALS_VAR_BEFORE] = call_user_func($this->AdviceName, $Arguments);
        }
    }
    
    /**
     * Execute the after Join and store the result in the corresponding globals
     * @param string $methodName Name of the original method in the __call() function
     * of the aspected object, it is ignored.
     * @param mixed $Arguments The arguments of the original __call() function, 
     * it is ignored.
     */
    private function executeJoinAfter(string $methodName, $Arguments = array())
    {
        if (is_array($Arguments))
        {
            $GLOBALS[self::GLOBALS_VAR_AFTER] = call_user_func_array($this->AdviceName, $Arguments);
        }
        else
        {
            $GLOBALS[self::GLOBALS_VAR_AFTER] = call_user_func($this->AdviceName, $Arguments);
        }
    }
    
    /**
     * Execute the around Join and pass its result in the corresponding globals. It
     * also creates the callable array to run the original method in the around
     * advice.
     * @param string $methodName Name of the original method in the __call() function
     * of the aspected object.
     * @param string $Arguments The arguments of the original __call() function.
     * @return mixed Returns the result of the advice.
     */
    private function executeJoinAround(string $methodName, $Arguments = array())
    {
        //Check if it a static method.
        if (strpos($methodName, "::") !== false)
        {
            //Create a callable array with the classe name, the method name and 
            //the arguments.
            $funcParts = explode("::", $methodName);
            $GLOBALS[self::GLOBALS_CALL_AROUND] = array($funcParts[0], 
                str_replace("()", "", $funcParts[1]), 
                $Arguments);
        }
        //Otherwise it is a standard class method
        else
        {
            //Create a callable array with the class name and new, the method 
            //name and the arguments.
            $funcParts = explode("->", $methodName);
            $GLOBALS[self::GLOBALS_CALL_AROUND] = array(new $funcParts[0], 
                str_replace("()", "", $funcParts[1]), 
                $Arguments);
        }
        $GLOBALS[self::GLOBALS_VAR_AROUND] = call_user_func($this->AdviceName);
        return isset($GLOBALS[self::GLOBALS_VAR_AROUND]) ? $GLOBALS[self::GLOBALS_VAR_AROUND] : "return";
    }
    
    /**
     * Execute the throw Join and store the result in the corresponding globals
     * @param string $methodName Name of the original method in the __call() function
     * of the aspected object, it is ignored.
     * @param Exception $Arguments The exception thrown by any advice of the original
     * method.
     */
    private function executeJoinThrow(string $methodName, $Arguments = array())
    {
        if (is_array($Arguments))
        {
            $GLOBALS[self::GLOBALS_VAR_THROW] = call_user_func_array($this->AdviceName, $Arguments);
        }
        else
        {
            $GLOBALS[self::GLOBALS_VAR_THROW] = call_user_func($this->AdviceName, $Arguments);
        }
    }
    
    /**
     * Execute the afterAnyway Join and store the result in the corresponding globals
     * @param string $methodName Name of the original method in the __call() function
     * of the aspected object, it is ignored.
     * @param mixed $Arguments The arguments of the original __call() function, 
     * it is ignored.
     */
    private function executeJoinAfterAnyway(string $methodName, $Arguments = array())
    {
        if (is_array($Arguments))
        {
            $GLOBALS[self::GLOBALS_VAR_ANYWAY] = call_user_func_array($this->AdviceName, $Arguments);
        }
        else
        {
            $GLOBALS[self::GLOBALS_VAR_ANYWAY] = call_user_func($this->AdviceName, $Arguments);
        }
    }
}
