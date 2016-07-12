<?php

namespace Core\Dispatching;

use Core\Engines\DependencyEngine as Container;

/**
 * Class tasked with building the route with a series of information extracted 
 * from the URL. With the route, it creates the Model, View and Controller, the 
 * Front-controller then does the rest.
 */
class Router
{
    /**
     * Constant containing the name for the models folder in the site structure.
     */
    const MODELS_FOLDER = "\\Models\\";
    
    /**
     * Constant containing the name for the views folder in the site structure.
     */
    const VIEWS_FOLDER = "\\Views\\";
    
    /**
     * Constant containing the name for the controllers folder in the site structure.
     */
    const CONTROLLERS_FOLDER = "\\Controllers\\";
    
    /**
     * Extract the information from the request and instantiate the route containing all 
     * required objects.
     * @param \Core\Dispatching\Request $request Request containing the data 
     * extracted from the URL.
     * @return \Core\Dispatching\Route The found route with all its properties instantiated.
     * @throws \Core\Exceptions\InvalidRequestException
     */
    public function find(\Core\Dispatching\Request $request) : \Core\Dispatching\Route
    {
        $modelName = self::MODELS_FOLDER . $request->getRouteName() . "Model";
        $viewName = self::VIEWS_FOLDER . $request->getRouteName() . "View";
        $controllerName = self::CONTROLLERS_FOLDER . $request->getRouteName() . "Controller";
        //Check if view and model classes exists.
        if (class_exists($viewName) && class_exists($modelName))  
        {
            $model = Container::getInstance()->set($modelName)->create();
            $view = Container::getInstance()->set($viewName)->create($request->getRouteName(), $model);
        }
        else
        {
            //Lift an invalid request exception.
            throw new \Core\Exceptions\InvalidRequestException("$viewName or $modelName");
        }
        //Check if the controller exist
        if (class_exists($controllerName)) 
        {
            $controller = Container::getInstance()->set($controllerName)->create($model);
        }
        else 
        {
            //Otherwise, do not create one, this is a static page.
            $controller = null;        
        }
        //Return a complete Route
        return Container::getInstance()->set("\Core\Dispatching\Route")->create($view, $controller);
    }
}
