<?php

namespace Core\Dispatching;

/**
 * Class containing the object for the front-controller. There is no need to 
 * store the model as it is already stored in both the view and the controller.
 * 
 * The class's properties are view only.
 */
class Route
{
    /*
     * The page's view instance.
     * @var \Core\MVC\CoreView Object instance for the page's view.
     */
    private $view;
    /**
     * The page's controller instance.
     * @var \Core\MVC\CoreController Object instance for the page's controller.
     */
    private $controller;

    /**
     * Class constructor.
     * @param \Core\Aspect\AspectManager $view Object instance for the page's view.
     * @param \Core\Aspect\AspectManager $controller Object instance for the page's controller.
     */
    public function __construct(\Core\Aspect\AspectManager $view, \Core\Aspect\AspectManager $controller = null) 
    {
        $this->view = $view;
        $this->controller = $controller;
    }
    
    /**
     * Get the view instance in the route.
     * @return \Core\Aspect\AspectManager Object instance for the page's view.
     */
    public function getView() : \Core\Aspect\AspectManager
    {
        return $this->view;
    }
    
    /**
     * Get the controller instance in the route.
     * @return \Core\Aspect\AspectManager Object instance for the page's controller.
     */
    public function getController() : \Core\Aspect\AspectManager
    {
        return $this->controller;
    }
}
