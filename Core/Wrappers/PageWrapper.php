<?php

namespace Core\Wrappers;

use Core\Engines\DependencyEngine as Container;

/**
 * The page wrappers is wrapped around the request and the MVC trio as an interceptor 
 * for all request for methods. It is used to make sure the page exist, the controller
 * has the requested methods and can send which methods the controller possesses for 
 * client logic.
 *
 */
class PageWrapper
{
    /**
     * Default exception route that shows the exception page.
     */
    const EXCEPTION_ROUTE = "Error/ShowError";
    
    /**
     * Instance of the route class that contains the MVC trio.
     * @var \Core\Dispatching\Route Route class instance.
     */
    protected $route;
    
    /**
     * Instance of the request class that contains the MVC trio.
     * @var \Core\Dispatching\request Request class instance.
     */
    protected $request;
    
    /**
     * Class constructor.
     */
    public function __construct()
    {
        $router = Container::getInstance()->set("\Core\Dispatching\Router")->create();
        try
        {
            $this->request = Container::getInstance()->set("\Core\Dispatching\Request")->create();
            //Get the route from the request
            $this->route = $router->find($this->request);
        } 
        catch (\Core\Exceptions\InvalidRequestException $ex)
        {
            $this->catchException($ex, $router);
        }
        catch (\Exception $ex) 
        {
            $this->catchException($ex, $router);
        }
    }
    
    /**
     * Execute the action on the controller while making sure the defined 
     * action does exist.
     */
    public function executeAction()
    {
        $router = new \Core\Dispatching\Router();
        try
        {
            //Run the method on the controler if it exists
            $ActionName = $this->request->getActionName();
            if (!empty($ActionName))
            {
                if (is_array($this->request->getActionParams()))
                {
                    call_user_func_array(array($this->route->getController(), 
                        $ActionName), $this->request->getActionParams());
                }
                else
                {
                    call_user_func(array($this->route->getController(), 
                        $$ActionName), $this->request->getActionParams());
                }
            }
        }
        catch (\BadMethodCallException $ex)
        {
            $this->catchException($ex, $router);
        }
        catch (\Exception $ex) 
        {
            $this->catchException($ex, $router);
        }
    }
    
    /**
     * Log the exception and display the error page instead.
     * @param \Exception $ex Exception caught.
     * @param \Core\Dispatching\Router $router Router to redirect the request.
     */
    private function catchException(\Exception $ex, \Core\Dispatching\Router $router)
    {
        //Log specific error
        $logger = new \Core\Exceptions\Logger();
        $logger->logException($ex);
        //Display error page 
        $this->request = Container::getInstance()->set("\Core\Dispatching\Request")->create(self::EXCEPTION_ROUTE);
        //Save the exception temporarly
        $_SESSION["SAVED_EXCEPTION"] = $ex;
        //Get the route from the request
        $this->route = $router->find($this->request);
    }
    
    /**
     * Display the data contained in the View with the application of a template
     * if applicable.
     * @return string HTMl structure of the View's data
     */
    public function display()
    {
        return $this->route->getView()->output();
    }
    
    /**
     * Check if the method exists in the controller.
     * @param string $methodName name of the method.
     * @return bool true if the method exists, false if not.
     */
    public function methodExists(string $methodName) : bool
    {
        return in_array($methodName, get_class_methods($this->route->getController()));
    }
}
