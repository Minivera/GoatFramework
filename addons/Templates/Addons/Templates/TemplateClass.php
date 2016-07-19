<?php

namespace Addons\Templates;

use Core\Engines\DependencyEngine as Container;

/**
 * Templating class who's job is to load the HTMl template and translate it to 
 * a php includable file.
 */
class TemplateClass
{
    const BASE_TEMPLATE_FOLDER = DIRECTORY_SEPARATOR . "Templates". DIRECTORY_SEPARATOR;
    
    /**
     * The current route to the page, to show in the template.
     */
    private $route;
    
    /**
     * Template path to transform and include.
     */
    private $template;
    
    /**
     * Array containing the full template structure as HTMl nodes.
     * @var array of HTMlNodes instances. 
     */
    private $templateStructure = null;
    
    /**
     * Class constructor.
     * @param string $Route The current view's route.
     */
    public function __construct(string $Route)
    {
        $config = Container::getInstance()->set("\Config\Config")->create();
        $this->route = $Route;
        $this->template = self::BASE_TEMPLATE_FOLDER . $Route;
        //Load the DOM template
        $this->templateStructure = Container::getInstance()
                ->set("\Addons\Templates\Html\HtmlDocument")
                ->create($config->dataFolder . $this->template . ".html");
    }
    
    public function show()
    {
        $config = Container::getInstance()->set("\Config\Config")->create();
        include $config->generatedCodeFolder . $this->template . ".php";
    }
    
    public function generate(array $modelData, string $ModelException = null)
    {
        $translator = Container::getInstance()->set("\Core\Engines\TranslationEngine")->create();
        $config = Container::getInstance()->set("\Config\Config")->create();
        //Add the route, the data and the exception.
        $fullData = array_merge($modelData, array("Route" => $this->route));
        if (isset($ModelException))
        {
            $fullData["Exception"] = $translator->translateException($ModelException);
        }
        //Start the output buffer with the translator as a callable
        ob_start(array($translator, "translate"));
        //Echo the escript with the variables in the current scope
        echo $this->templateStructure->generateScript($fullData);
        //If the template is static or cached
        if ($this->templateStructure->getIsStatic() || 
                $this->templateStructure->getIsCached())
        {
            //Put the generated and static document in the folder for later inclusion
            file_put_contents($config->generatedCodeFolder . $this->template . ".php", ob_get_contents());
        }
        //Send the output buffer to the browser, translator is called
        ob_end_flush(); 
    }
    
    public function isCached()
    {
        $config = Container::getInstance()->set("\Config\Config")->create();
        return file_exists($config->generatedCodeFolder . $this->template . ".php") &&
                !$this->hasToBeGenerated($this->templateStructure);
    }
    
    private function hasToBeGenerated(\Addons\Templates\Html\HtmlDocument $template) : bool
    {
        $config = Container::getInstance()->set("\Config\Config")->create();
        //If  the file doesn't exists
        if (!file_exists($config->generatedCodeFolder . $this->template . ".php"))
        {
            return true;
        }
        //If the HTML file is newer than the PHP file
        if (filemtime($config->generatedCodeFolder . $this->template . ".php") <
                filemtime($config->dataFolder . $this->template . ".html"))
        {
            return true;
        }
        //if the page is static, never generate
        if ($template->getIsStatic())
        {
            return true;
        }
        //If the page is cached, check if the current template has been modified since.
        if ($template->getIsCached())
        {
            //If the page is older than the the minimum cache time
            if (time() - $template->getCacheLenght() > filemtime($config->
                    generatedCodeFolder . $this->template . ".php"))
            {
                return true;
            }
        }
        return false;
    }
}
