<?php

namespace Addons\Database;

/**
 * Constants and configuration file for the Database addon.
 */
class Config
{
    /**
     * Path to the query stored in the session, the query,s result will be 
     * shortened and stored in the session fro future uses.
     * 
     * If a insert or other query that changes the data is run, the session is
     * reset, otherwise, each request reloads the same data according to the session
     * length config.
     */
    const SESSION_QUERY_STORE = "session_query_store";
    
    /**
     * Time in second a query in the session should be kept, the query
     * is rerun if it is older than the current time - this length.
     */
    const SESSION_STORE_LENGTH = 120;
    
    /**
     * Date in the session for when the query store has been updated last.
     */
    const QUERY_STORE_DATE = "query_store_date";
}
