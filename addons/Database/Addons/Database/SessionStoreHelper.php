<?php

namespace Addons\Database;

/**
 * Session store whose job is to save a function result and call in session for a fixed
 * amount of time so that result can be reloaded if needed to save time.
 * 
 * TODO: Make it contains more than one function at a time.
 */
class SessionStoreHelper
{
    /**
     * Set the current session store with the data given in parameters. To identify the 
     * given function, it encodes the data in json to make sure only the same function will
     * receive its store.
     * @param mixed $result Result of the funciton to store in cache.
     * @param string $functioname Function name to store.
     * @param array $functionData Array of the function's arguments.
     */
    public function setStore($result, string $functioname, ...$functionData)
    {
        //Create the store array, it is made of the json_encoded function data and
        //the data to store.
        $arrayStore = array(
                "identifier" => json_encode(array($functioname, $functionData)),
                "content"    => $result
            );
        $_SESSION[\Addons\Database\Config::SESSION_QUERY_STORE] = $arrayStore;
        $_SESSION[\Addons\Database\Config::QUERY_STORE_DATE] = time();
    }
    
    /**
     * Get the store in session for the given function if it exists.
     * @param string $functioname Function name in the store.
     * @param array $functionData Array of the function's arguments.
     * @return mixed Return null if the store is empty or contains another function,
     * returns the function results otherwise.
     */
    public function getSessionStore($functioname, ...$functionData)
    {
        //If there is no store
        if (!isset($_SESSION[\Addons\Database\Config::SESSION_QUERY_STORE]))
        {
            return;
        }
        //If the current store is older than the configurer maximum time
        if (($_SESSION[\Addons\Database\Config::QUERY_STORE_DATE] + 
            \Addons\Database\Config::SESSION_STORE_LENGTH) < time())
        {
            return;
        }
        $encodedIdentifier = json_encode(array($functioname, $functionData));
        $store = $_SESSION[\Addons\Database\Config::SESSION_QUERY_STORE];
        if ($store["identifier"] === $encodedIdentifier)
        {
            var_dump($store);
            return $store["content"];
        }
    }
    
    /**
     * Unset the session store and all its values.
     */
    public function unsetStore()
    {
        unset($_SESSION[\Addons\Database\Config::SESSION_QUERY_STORE]);
        unset($_SESSION[\Addons\Database\Config::QUERY_STORE_DATE]);
    }
    
    /**
     * Checks if the given function is identified in the session store and if
     * it is still valid.
     * @param string $functioname Function name in the store.
     * @param array $functionData Array of the function's arguments.
     * @return boolean Returns true if the function exists and is valid, false if not.
     */
    public function inSessionStore(string $functioname, ...$functionData)
    {
        if (!isset($_SESSION[\Addons\Database\Config::SESSION_QUERY_STORE]))
        {
            return false;
        }
        //If the current store is older than the configurer maximum time
        if (($_SESSION[\Addons\Database\Config::QUERY_STORE_DATE] + 
            \Addons\Database\Config::SESSION_STORE_LENGTH) < time())
        {
            return false;
        }
        $encodedIdentifier = json_encode(array($functioname, $functionData));
        $store = $_SESSION[\Addons\Database\Config::SESSION_QUERY_STORE];
        if ($store["identifier"] === $encodedIdentifier)
        {
            return true;
        }
        return false;
    }
}
