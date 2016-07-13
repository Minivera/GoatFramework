<?php

namespace Core\Engines;

/**
 * Singleton dependency injection container and object factory
 * 
 * This engine takes care of all class creation in the system for classes that 
 * allow its usage. When it receives a class creation request, it can do one of 
 * four things :
 * 
 * <ul>
 *  <li>Create a default implementation of a class using dependency injection. 
 * (No parameters needed)</li>
 *  <li>Create a parameterized object using the parameters given. It can also 
 * use dependency injection to fill in the gaps.</li>
 *  <li>Create a singleton version of the class using the default implementation.</li>
 *  <li>Create an aspect if the given class implements the aspect interface.</li>
 * </ul>
 * 
 * Note that addons registered are loaded with the addon helper and used in place of
 * their core counterparts when any class is created.
 */
class DependencyEngine extends \Core\Structures\Singleton
{
    
    /**
     * Addons helper instance used to ensure the correct loading of addons and
     * their redefined classes.
     * @var \Core\HelperClasses\AddonHelper Helper instance. 
     */
    private $addonhelper;
    
    /**
     * Container configuration that allow some parameterization.
     * 
     * The possible configurations are as follow:
     * 
     * <ul>
     * <li>allow_aspect : Allow the creation of aspected classes via the aspect factory, true by default.</li>
     * <li>allow_inject_dependencies : Allow the dependency injection to work, true by default. </li>
     * <li>allow_singleton : Allow the use of singleton. true by default.</li>
     * <li>create_as_pure : identify in an array of fully qualified classnames 
     * classes that should be only created with user defined parameters, not with 
     * dependency injection. Empty by default, ignored if allow_inject_dependencies 
     * is false. 
     * TODO: Make it usefull</li>
     * </ul>
     * @var array Array of configuration, contains default configuration until reconfigured. 
     */
    private $configuration = array(
        "allow_aspect" => true,
        "allow_inject_dependencies" => true,
        "allow_singleton" => true,
        "create_as_pure" => array()
    );
    
    /**
     * Container,s class to create or obtain when asked. Must be set with get 
     * before creation.
     * @var type 
     */
    private $classname;
    
     /**
     * Protected constructor to prevent creating a new instance of the
     * class via the new operator from outside of this class.
     */
    protected function __construct()
    {
        //Load the Addon helper
        $this->addonhelper = new \Core\HelperClasses\AddonHelper();
        parent::__construct();
    }
    
    /**
     * Replace existing keys in the configuration array with the on present in 
     * the given array, if any.
     * @param array $configuration Configuration array containing valid keys.
     * @return DependencyEngine returns its instance for function chaining.
     */
    public function configure(array $configuration) : DependencyEngine
    {
        foreach ($configuration as $key => $value)
        {
            if (key_exists($key, $this->configuration))
            {
                $this->configuration[$key] = $value;
            }
        }
        return static::$instance;
    }
    
    /**
     * Set the internal classname with the one given in parameters, it also check if 
     * there is an available addon for the class.
     * @param string $classname Class to instantiante.
     * @return \Core\Engines\DependencyEngine Return the singleton instance for funciton chaining.
     * @throws \Core\Exceptions\ClassNotFoundException
     */
    public function set(string $classname) : DependencyEngine
    {
        if (class_exists($classname))
        {
            $this->classname = $this->addonhelper->checkAvailableAddons($classname);
        }
        else
        {
            throw new \Core\Exceptions\ClassNotFoundException($classname);
        }
        return static::$instance;
    }
    
    /**
     * Get the current class name after it has be checked for addons availability.
     * @return string Class name to instanciante.
     */
    public function get() : string
    {
        return $this->classname;
    }
    
    /**
     * Create the instance for the given class name according to its implementations 
     * and extentions. See the class documentation for more details.
     * @param array $params The parameters to use for the class constructor, null by default.
     * @return mixed The instance of the request class or null if an error occurred.
     * @throws \Core\Exceptions\SkippedStepException
     */
    public function create(...$params)
    {
        if (empty($this->classname))
        {
            throw new \Core\Exceptions\SkippedStepException("get() must be called before create()");
        }
        $reflection = new \ReflectionClass($this->classname);
        $implements = $reflection->getInterfaceNames();
        $extends = $reflection->getExtensionName();
        //Check for aspect
        if (in_array(\Core\Aspect\Aspect::class, $implements) && 
                $this->configuration["allow_aspect"])
        {
            //Create the aspect
            return $this->createAspect($params);
        }
        //Check for singleton extention
        if (\Core\Structures\Singleton::class === $extends && 
                $this->configuration["allow_singleton"])
        {
            return ${$this->classname}::getInstance();
        }
        //If both skipped, we create a standard class, check if there is parameters
        if (null === $params && $this->configuration["allow_inject_dependencies"])
        {
            //If not, create the class with dependency injection
            return $this->createWithDependencies();
        }
        else 
        {
            //Create the class with standard parameters.
            return $this->createWithUserParams($params);
        }
        //if, in all cases, the class could not be created, return null
        return null;
    }
    
    /**
     * Create the required class with all of its dependencies already created.
     * 
     * Not that this cannot accept builtIn types, though it could be programmed, 
     * it breaks the idea of a dependency injector.
     * @return mixed Return the created class or throws an exception if an error occurred.
     * @throws \BadMethodCallException If the given class has a builtin type as 
     * one of its parameters.
     * @throws \Core\Exceptions\ClassNotFoundException if the required class for 
     * dependency injection is not found, this exception is lifted.
     */
    private function createWithDependencies()
    {
        $reflection = new \ReflectionClass($this->classname);
        //Check if the class has a constructor defined
        if (null === $reflection->getConstructor() 
                && $reflection->IsInstantiable())
        {
            //If not, build the class right away.
            return $reflection->newInstanceArgs();
        }
        //If yes, build its parameters array
        $params = $reflection->getConstructor()->getParameters();
        $arrayParameters = array();
        foreach ($params as $param)
        {
            if ($param->isOptional())
            {
                //If the parameter is optionnal, get the default value
                array_push($arrayParameters, $param->getDefaultValue());
                continue;
            }
            //If not, create the dependency, throw an error on basic types, do 
            //not use basic types with dependency injection.
            if ($param->getType()->isBuiltin())
            {
                throw new \BadMethodCallException("Dependency injection cannot be used with builtIn types");
            }
            if (class_exists((string)$param->getType()))
            {
                //Send this class type back into the depedency injector
                $class = self::getInstance()->set((string)$param->getType())->create();
                array_push($arrayParameters, $class);
            }
            else
            {
                throw new \Core\Exceptions\ClassNotFoundException((string)$param->getType());
            }
        }
        return $reflection->newInstanceArgs($arrayParameters);
    }
    
    /**
     * Creates the requested class with the user parameters.
     * @param array $params Array of parameters to send to the constructor.
     * @return mixed Return the request class with its parameters.
     */
    private function createWithUserParams(array $params = array())
    {
        //TODO: Find a way to implement dependency injection of missing parameters... Needed?
        //Call constructor and split parameters array.
        return new $this->classname(...$params);
    }
    
    /**
     * Crates the requested class as an aspected class.
     * @param array $params parameters to send to the constructor.
     * @return mixed Returns and instance of Aspect manager containing the 
     * required class with its activated aspect.
     */
    private function createAspect(array $params = array())
    {
        return \Core\Aspect\AspectFactory::create($this->classname, $params);
    }
}
