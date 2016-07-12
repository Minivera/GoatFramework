<?php

namespace Core\Aspect;

use ReflectionMethod;

/**
 * Class that defines a Point Cut. It allows to create a new instance of any
 * pointcut and resolve it according to predefined parameters.
 * 
 * Current predefined parameters for pointcuts :
 * <ul>
 *  <li>private|public|protected, will only call methods with the specified 
 * visibility. Can use negation (!) to NOT use method of that visibility.</li>
 *  <li>WildCards(*), will check methods names according to the wildcard. *() will 
* call anything, *test() check for method that ends with test, test*() check
* fot the method that starts with test, namespace\* call any method of namespace, 
* *::test() will call static method of any object and so on.</li>
* <li>It can use any of the following : Class methods with classname->methodname(), 
* static methods with classname::methodname() and functions with functionname().</li>
 * </ul>
 * Never specify arguments when calling a PointCut.
 * 
 * @author gst-pierre
 */
class PointCut
{
    
    /**
     * Definition of the constant for the class method type. Used when the method name
     * is similar to classname->method()
     */
    const TYPE_CLASS_METHOD = "classmethod";

    /**
     * Definition of the constant for the static method type. Used when the method name
     * is similar to classname::method()
     */
    const TYPE_STATIC_METHOD = "staticmethod";

    /**
     * Definition of the constant for the standard function type. Used when the method name
     * is similar to function()
     */
    const TYPE_FUNCTION = "standardfunction";

    /**
     * Definition of the constant for the class property type. Used when the method name
     * is similar to classname->property
     */
    const TYPE_CLASS_PORPERTY = "classproperty";
    
    /**
     * Constant for the validation of the point cut when constructed, this regex
     * checks if the point cut is made of namespace->method(), namespace::method() or function()
     */
    const REGEX_POINTCUT_VALID = "/^((?:\\S+\\\\)+\\S+(?:->|::)[A-Za-z*]+\\(\\)|[A-Za-z*]+\\(\\))$/";
    
    /**
     * The string containing the point cut to resolve
     * @var string 
     */
    private $PointCutString = "";
    
    /**
     * Initialize the Point cut with the required string and validates the input
     * @param string $pointCut Point cut string to initialize.
     * @throws UnexpectedValueException
     */
    public function __construct(string $pointCut)
    {
        if (preg_match(self::REGEX_POINTCUT_VALID, $pointCut)) 
        {
            $this->PointCutString = $pointCut;
        }
        else
        {
            throw new \UnexpectedValueException("The pointcut '$pointCut' is not valid, check your syntax.");
        }
    }
    
    /**
     * Resolve the PointCut with the method name passed in parameters. It will follow
     * the basic guidelines of the PointCut and return the result.
     * @param string $methodName Fully qualified name of the method to resolve.
     * @return bool True if the given method is covered by the Point cut, false if not.
     */
    public function resolve(string $methodName) : bool
    {
        //Check if the namespace can be resolved
        $namespaceFound = $this->resolveNamespace(
                substr($methodName, 0, strrpos($methodName, "\\")),
                substr($this->PointCutString, 0, strrpos($this->PointCutString, "\\")));
        //Then check if the function name can be resolved
        $functionFound = $this->resolveFunction(
                substr(strrchr($methodName, "\\"), 1),
                substr(strrchr($this->PointCutString, "\\"), 1));
        if ($namespaceFound && $functionFound)
        {
             return $this->resolveVisibility($methodName);
        }
        return false;
    }
    
    /**
     * Resolve the namespace part of the Pointcut and the method.
     * @param string $MethodNamespace The namespace part of the method
     * @param string $PointCutNamespace The Namespace part of the PointCut
     * @return mixed Return 1 if the resolution worked, 0 if it didn't or false
     * if an error occured.
     */
    private function resolveNamespace(string $MethodNamespace, string  $PointCutNamespace)
    {
        //Remove any public/private or protected for visibility
        $NewNamespace = str_replace("public ", "", $PointCutNamespace);
        $NewNamespace = str_replace("private ", "", $NewNamespace);
        $NewNamespace = str_replace("protected ", "", $NewNamespace);
        //we build a regex to compare with the method name
        $regex = "/^";
        //we change any wildcard (*) into a .* and add it to the regex
        $regex .= str_replace("*", ".*", $NewNamespace);
        //we replace all \ with a \\ for the regex
        $regex = str_replace("\\", "\\\\", $regex);
        //finish the regex
        $regex.= "$/";
        return \preg_match($regex, $MethodNamespace);
    }
    
    /**
     * Resolve the function part of the pointcut and the method
     * @param string $MethodName The function part of the point cut
     * @param string $PointCutName The funciton part of the namespace.
     * @return mixed Return 1 if the resolution worked, 0 if it didn't or false
     * if an error occured.
     */
    private function resolveFunction(string $MethodName, string $PointCutName)
    {
        //we build a regex to compare with the method name
        $regex = "/^";
        //we change any wildcard (*) into a .* and add it to the regex
        $regex .= str_replace("*", ".*", $PointCutName);
        //we change any parathesis into a \( or a \)
        $regex = str_replace("(", "\\(", $regex);
        $regex = str_replace(")", "\\)", $regex);
        //finish the regex
        $regex .= "$/";
        return \preg_match($regex, $MethodName);
    }
    
    /**
     * Resolve the visibility part of the pointcut, it checks if there is an identifier 
     * in the point cut (Public/private/protected) and then check if the method
     * is the same.
     * @param string $methodName The whole method to test
     * @return bool Return true if the method valid or if there is no visibility 
     * defined, return false if not.
     */
    private function resolveVisibility(string $methodName) : bool
    {
        //find if the point cut specify any visibility.
        if (strpos($this->PointCutString, 'public') !== false) 
        {
            $reflection = new ReflectionMethod($this, $methodName);
            if (!$reflection->isPublic()) 
            {
                return false;
            }
        }
        else if (strpos($this->PointCutString, 'private') !== false) 
        {
            $reflection = new ReflectionMethod($this, $methodName);
            if (!$reflection->isPrivate()) 
            {
                return false;
            }
        }
        else if (strpos($this->PointCutString, 'protected') !== false) 
        {
            $reflection = new ReflectionMethod($this, $methodName);
            if (!$reflection->isProtected()) 
            {
                return false;
            }
        }
        return true;
    }
}
