<?php

namespace Core\Dispatching;

/**
 * The class analyses the request's URL and extract the necessary information 
 * for the front-controller to create the required MVC trio.
 * 
 * The route used by the Front-Controller is written as:
 * <pre>
 * PageURL\Subfolder\ClassnamePrefix\Action\Param1\Param2\...\ParamN
 * </pre>
 * 
 * The request extracts the subfolder and classname prefix. This allows the page to 
 * be separated in folder for an easier management. 
 * 
 * Note that, according to the PSR-0 standards, the class namespace must be the
 * folder structure. The router calls files in the right folder (Controller, 
 * Models or Views) and search for the right subfolder.
 */
class Request
{
    /**
     * Default web route, it will call the index view and model at the root of
     * the View and Model folders.
     */
    const DEFAULT_ROUTE = "Index";
    
    /**
     * Routename given to the view to tell where it came from. 
     * It is written as a pair Subfolder/ClassPrefix and can be used to send 
     * request to the controller.
     * @var string Current request Routename.
     */
    private $routeName;
    
    /**
     * Name of the action to execute on the controller, if any.
     * @var string Action name, null by default.
     */
    private $actionName = null;
    
    /**
     * Optional parameter for the action on the controller.
     * @var array Parameters array, they must be valid according to the action
     * specification or an exception will be raised.
     */
    private $actionParams = array();
    
    /**
     * Class constructor.
     * @param string $forceUrl Allow the system to bypass the current URl and 
     * instead takes this one. useful for early redirects.
     */
    public function __construct($forceUrl = null)
    {
        if (isset($forceUrl))
        {
            $path = $forceUrl;
        }
        else
        {
            $path = trim(parse_url(filter_input(INPUT_SERVER, "REQUEST_URI"), PHP_URL_PATH), "/");
            $path = $this->removeBasePath($path);
            //Check if the current logged user can access this request, if not, 
            //the request will automatically redirect to the default page.
            \Core\Dispatching\SecurityManager::checkSecurity($path);
        }
        extract($this->buildVarFromRoute($path));
        //Build the routename, either from the subfolder + class prefix or the default route if both are empty.
        $this->routeName = !isset($subfolder, $prefix) ? self::DEFAULT_ROUTE : ucfirst($subfolder) . "\\" . ucfirst($prefix);
        //Check for the action, it is possible to run the action through the post instead.
        if (isset($action)) 
        {
            $this->actionName = $action;
        }
        else 
        {
            $action = filter_input(INPUT_POST, "action");
            $this->actionName = isset($action) ? $action : null;
        }
        //Check for the action params, the system also allows the passage of 
        //parameters through the post.
        if (isset($params)) 
        {
            $this->actionParams = explode("/", $params);
        }
        else
        {
            $params = filter_input(INPUT_POST, "params");
            $result = json_decode($params);
            if (json_last_error() === JSON_ERROR_NONE) 
            {
                // Decode the params as JSON if necessary.
                $this->actionParams = $result;
            }
            else
            {
                $this->actionParams = $params;
            }
        }
    }
    
    /**
     * Get the current route name.
     * @return string Route name given by the request.
     */
    public function getRouteName() : string
    {
        return $this->routeName;
    }
    
    /**
     * Get the action name to send to the controller, if any.
     * @return string Action name extracted from the request or the POST.
     */
    public function getActionName()
    {
        return $this->actionName;
    }
    
    /**
     * Get the parameters to send to the controller action if any.
     * @return array Array containing all parameters extracted from the request or the POST.
     */
    public function getActionParams()
    {
        return $this->actionParams;
    }
    
    /**
     * Resolve the site basepath and removes it from the given path.
     * @param string $path current incorrect path.
     * @return string Path without the base path.
     */
    private function removeBasePath(string $path) : string
    {
        //Get the current site basepath from this filename.
        $basePath = substr(__DIR__, 0, strpos(__DIR__, __NAMESPACE__));
        //For each element of the basepath, remove it if it is found in the URL
        foreach (\explode("\\", trim($basePath, "\\")) as $value)
        {
            if (strpos($path, $value) === 0)
            {
                $path = str_replace($value . "/", "", $path);
            }
        }
        return $path;
    }
    
    /**
     * Extract the data from the route and places it in an array containing; 
     * $subfolder, $prefix, $action and $params.
     * @param string $path Route to extract the data from.
     * @return array Associative array containing the variables names and 
     * the data they should contain.
     */
    private function buildVarFromRoute(string $path) : array
    {
        $arrayVarNames = array("subfolder", "prefix", "action", "params");
        $explodedPath = \explode("/", trim($path, "/"), 4);
        $final = array("subfolder" => null, "prefix" => null, "action" => null, "params" => null);
        array_walk($explodedPath, function($val, $key) use(&$final, $arrayVarNames)
        {
                $final[$arrayVarNames[$key]] = $val;
        });
        return $final;
    }
}
