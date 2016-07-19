<?php

namespace Addons\Templates;

use Core\Engines\DependencyEngine as Container;

/**
 * Extended page wrapper that adds the functionality to load the cache php file 
 * if possible.
 */
class PageWrapper extends \Core\Wrappers\PageWrapper
{
    /**
     * Tell the page wrapper if the page it wraps is cached or not.
     * @var bool Is this page static? False by default. 
     */
    private $isCached = false;
    
    /**
     * Overridden class constructor.
     */
    public function __construct()
    {
        $request = Container::getInstance()->set("\Core\Dispatching\Request")->create();
        $template = Container::getInstance()->set("\Addons\Templates\TemplateClass")->create($request->getRouteName());
        //If the template is cached
        if ($template->isCached())
        {
            //Show the cached file
            $this->isCached = true;
            $template->show();
        }
        else
        {
            //Otherwise, generate as core.
            parent::__construct();
        }
    }
    
   /**
    * Overridden method that executes the action on the constructor.
    */
    public function executeAction()
    {
        //Is the page cached?
        if ($this->isCached)
        {
            //Nothing to execute
            return;
        }
        parent::executeAction();
    }
    
    /**
     * Display the data contained in the View with the application of a template
     * if applicable.
     * @return string HTMl structure of the View's data
     */
    public function display()
    {
        //If the page was cached
        if ($this->isCached)
        {
            //Nothing to do, we already showed the page
            return;
        }
        return $this->route->getView()->output();
    }
}
