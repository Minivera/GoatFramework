<?php

namespace Core\HelperClasses;

/**
 * helper class used to load and request informations from the addon registration file.
 */
class AddonHelper
{
    /**
     * Path to the file containing all the plugins information.
     */
    const PLUGINS_FILE = "./RegisteredPlugins.xml";
    
    /**
     * The loaded Addon XML file used in the class.
     * @var \SimpleXMLElement Root node of the XML file. 
     */
    private $AddonsFile;
    
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->AddonsFile = simplexml_load_file(self::PLUGINS_FILE);
    }
    
    /**
     * Check if an addon is available for the given class name. Due to the 
     * iterative nature of XML, only the first hit addon with the given class name
     * redefined will be given.
     * 
     * TODO: Find a way to fuse multiple addons, if possible.
     * @param string $classname name of the class to search for.
     * @return string Return the class name from the addon or the class if no addon was found.
     */
    public function checkAvailableAddons(string $classname) : string
    {
        //If the Addon file was sucessfully loaded
        if (is_bool($this->AddonsFile))
        {
            return $classname;
        }
        //If there is no childrens
        if (empty($this->AddonsFile->children()))
        {
            return $classname;
        }
        foreach ($this->AddonsFile->children() as $addons)
        {
            foreach ($addons->Redefines->children() as $redefine)
            {
                if($redefine["old_class"]->__toString() === $classname)
                {
                    return $addons["namespace"] . "\\" . $redefine;
                }
            }
        }
        return $classname;
    }   
}
