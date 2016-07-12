<?php

namespace Core\MVC;

use Core\Engines\DependencyEngine as Container;

/**
 * Class tasked with managing all calls sent by the site's index. With the URL, it 
 * instantiate a request and a router where the MVC trio is created. It wraps
 * the trio in the page wrapper and send a signal to call a method on the controller
 * if needed.
 * 
 * The Front-Controller does not check for errors, this is in the hands of the page wrapper.
 * 
 * The route used by the Front-Controller is written as:
 * <pre>
 * PageURL\Subfolder\ClassnamePrefix\Action\Param1\Param2\...\ParamN
 * </pre>
 */
class FrontController
{
    /**
     * Page wrapper for the current request, it contains all information about the
     * page and makes sure everything is working.
     * @var \Core\Wrappers\PageWrapper Instance of the page Wrapper. 
     */
    private $wrapper;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->wrapper = Container::getInstance()->set("\Core\Wrappers\PageWrapper")->create();
    }

    /**
     * Send a signal to the wrapper to run the action and display the result.
     */
    public function run()
    {
        $this->wrapper->executeAction();
        $this->wrapper->display();
    }
}
