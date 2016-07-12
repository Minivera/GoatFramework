<?php

/**
 * Application autoloader who's tasked with loading the classes for the web 
 * application. 
 * 
 * All the application namespaces must follow the PSR-0 standard for them to be 
 * successfully loaded.
 */
class Autoloader
{
    /**
     * Default extention for files in PHP.
     * @var string Php file extention. 
     */
    private $fileExtension = '.php';
    
    /**
     * Full path for file inclusion in the application.
     * @var string Current class path.
     */
    private $includePath = __DIR__;
    
    /**
     * Symbol used for Namespace separators by PHP.
     * @var string Namespace separator symbol. 
     */
    private $namespaceSeparator = '\\';
    
    /**
     * Register the autoloader on the SPL stack.
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }
    
    /**
     * unregister the autoloader on the SPL stack.
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }
    
    /**
     * Load the specified class or interface.
     * @param string $className Name of the class to load.
     */
    public function loadClass(string $className)
    {
        $fileName = $this->obtainCurrentClass($className);
        //We force the loading of the class, otherwise, an exception is lifted.
        //If the inclusion path is null, we load the class from the root directory.
        $fileName = ($this->includePath !== null ? $this->includePath . DIRECTORY_SEPARATOR : '') . $fileName;
        if (file_exists($fileName))
        {
            require $fileName;
        }
    }
    
    /**
     * Obtain the current defined class in the system,.
     * @param string $className The name of the class to find in the system.
     * @return string The complete filename of the class to include.
     */
    public function obtainCurrentClass(string $className) : string
    {
        $fileName = '';
        $namespace = '';
        //Check if it is possible to extract the namespace from the class name.
        if (false !== ($lastNsPos = strripos($className, $this->namespaceSeparator))) 
        {
            //Extract the namespace
            $namespace = substr($className, 0, $lastNsPos);
            //Extract the real class name.
            $className = substr($className, $lastNsPos + 1);
            //Replace namespace separators with the system's directory separators
            $fileName = str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
        }
        $fileName .= $className . $this->fileExtension;
        return $fileName;
    }
}
