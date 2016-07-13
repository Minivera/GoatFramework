<?php

namespace Core\Engines;

use Core\Engines\DependencyEngine as Container;

/**
 * Engine tasked with translating every request for text in the system. It loads 
 * the correct locale file and display the text in the correct language.
 */
class TranslationEngine
{
    const REGEX_FIND_STRING = "/%([^%]+)%/";
    
    /**
     * Path to the locales files.
     */
    const LOCALE_PATH = "Locale/";
    
    /**
     * Array containing the local file in a way that can be easily manipulated. 
     * It maintains the Keyname:value format in an array.
     * @var array Locales in an associative array. 
     */
    private $loadedLocales = array();
    
    /**
     * Class constructor that loads the class with all the site's locales.
     */
    public function __construct()
    {
        $config = Container::getInstance()->set("\Config\Config")->create();
        $localecontent = file_get_contents(self::LOCALE_PATH . $config->language . ".locale");
        $arraylocale = explode("\n", $localecontent);
        foreach ($arraylocale as $value)
        {
            //if the first character is a # or is empty
            if (empty($value) || $value[0] === "#")
            {
                // go to the next line
                continue;
            }
            $components = explode(":", $value, 2);
            $this->loadedLocales[strtolower($components[0])] = $components[1];
        }
    }
    
    /**
     * Translate all occurrences of a translatable key in the text with the loaded
     * locale. All string in the text identified as %word% are checked if a locale
     * exists for that key and replaces it with the locale text.
     * @param mixed $text Text to check for locale keys.
     * @return string Translated text.
     */
    public function translate($text) : string
    {
        $text = (string)$text;
        $matches = array();
        preg_match_all(self::REGEX_FIND_STRING, $text, $matches);
        if (isset($matches[1]))
        {
            foreach ($matches[1] as $val) 
            {
                //Check if the key exists in the currently loaded locales
                if(array_key_exists(strtolower($val), $this->loadedLocales))
                {
                    $text = str_replace("%$val%", $this->loadedLocales[strtolower($val)], $text);
                }
            }
        }
        return $text;
    }
    
    /**
     * Translate an exception according to its class name in the loaded locales.
     * @param \Core\Engines\Exception $Exception Lifted exception to translate.
     * return string Translated text.
     * @return string The exception description form the locale file.
     */
    public function translateException(\Throwable $Exception) : string
    {
        $classname = get_class($Exception);
        //Check if the key exists in the currently loaded locales
        if(array_key_exists(strtolower($classname), $this->loadedLocales))
        {
            return $this->loadedLocales[strtolower($classname)];
        }
    }
}
