<?php

namespace Core\Dispatching;

use Core\Engines\DependencyEngine as Container;

/**
 * Abstract class tasked with receiving calls from the request and ensuring the currently 
 * logged user has the rights to view this page.
 *
 * TODO: Redo this class, make it a singleton and allow configuration with the Index
 */
class SecurityManager
{
    /**
    * Constant for the right to connect to a page without any login.
    */
   const ANONYMOUS_LOGIN =  "anonymous";

   /**
    * Constant defining the rights to connect to a page as a logged user.
    */
   const USER_LOGIN =  "user";

   /**
    * Constant defining the rights to connect to a page as a logged administrator.
    */
   const ADMINISTRATOR_LOGIN = "administrator";

   /**
    * Constant defining the link to all other pages not defined in the system.
    */
   const ALL_OTHER_PAGES = "all_pages";
    
    /**
     * Array containing the configuration for rights on the pages. All pages fall on the 
     * anonymous per default rights. 
     * @var array Array of rights.
     */
    private static $rightsConfiguration = array(
        self::ANONYMOUS_LOGIN       => array(
            self::ALL_OTHER_PAGES
        ),
        self::USER_LOGIN   => array(
        ),
        self::ADMINISTRATOR_LOGIN => array(
        )
    );
    
    /**
     * Array of redirect configurations, when a page is accessed without the 
     * rights to it, it automatically redirect the user to the page configured here.
     * @var array Array for the redirect configurations.
     */
    private static $redirectConfiguration = array(
        self::USER_LOGIN    => "index.php",
        self::ADMINISTRATOR_LOGIN => "index.php"
    );
    
    //Prevent classe constructor, static class.
    private function __construct() {}
    
    /**
     * Check the route received to see i the currently connected user has 
     * the rights to it.
     * @param string $route Current page URL.
     */
    static public function checkSecurity(string $route)
    {
        //If the route is empty, we are accessing the index, return.
        if (empty($route))
        {
            return;
        }
        foreach(self::$rightsConfiguration[self::ADMINISTRATOR_LOGIN] as $value)
        {
            if(substr_compare($route, $value, 0, strlen($value)) === 0)
            {
                if (!isset($_SESSION[self::ADMINISTRATOR_LOGIN]))
                {
                    header("location:/" . self::$redirectConfiguration
                            [self::ADMINISTRATOR_LOGIN]);
                }
            }
        }
        foreach(self::$rightsConfiguration[self::USER_LOGIN] as $value)
        {
            if(substr_compare($route, $value, 0, strlen($value)) === 0)
            {
                if (!isset($_SESSION[self::USER_LOGIN]))
                {
                    header("location:/" . self::$redirectConfiguration
                            [self::USER_LOGIN]);
                }
            }
        }
    }
    
    /**
     * Connect the current user session as a user.
     */
    static public function connectUser()
    {
        $_SESSION[self::USER_LOGIN] = true;
    }
    
    /**
     * Connect the current user session as an administrator.
     */
    static public function connectAdministrator()
    {
        $_SESSION[self::ADMINISTRATOR_LOGIN] = true;
    }
    
    /**
     * Logout the current user as a user.
     */
    static public function logoutUser()
    {
        unset($_SESSION[self::USER_LOGIN]);
    }
    
    /**
     * Logout the current user as an Administrator.
     */
    static public function logoutAdministrator()
    {
        unset($_SESSION[self::ADMINISTRATOR_LOGIN]);
    }
    
    /**
     * Checks if the current user is logged as a user.
     * @return boolean True if the user is a user, false otherwise.
     */
    static public function isUser()
    {
        return isset($_SESSION[self::USER_LOGIN]);
    }
    
    /**
     * Checks if the current user is logged as an administrator.
     * @return boolean True if the user is an Admin, false otherwise.
     */
    static public function isAdministrator()
    {
        return isset($_SESSION[self::ADMINISTRATOR_LOGIN]);
    }
}
