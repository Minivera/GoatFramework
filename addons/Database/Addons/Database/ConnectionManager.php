<?php

namespace Addons\Database;

use PDO;
use Core\Engines\DependencyEngine as Container;

/**
 * Singleton that manages the connection to the database. It serves as an entry 
 * point for all request for tables in the database and contains the configured PDO
 * connection.
 * 
 * It also allows the caching of fetch queries, if the exact same query is called 
 * again after it has been stored in cache, it will load the stored query rather 
 * than request one from the database. 
 */
class ConnectionManager extends \Core\Structures\Singleton
{
    /**
     * Sprintf format for the connexion string. I accepts a driver, a host (with port),
     * a database name and a charset. 
     */
    const CONNECTION_STRING_FORMAT = "%s:host=%s;dbname=%s;charset=%s";
    
    /**
     * Database PDO connection.
     * @var PDO 
     */
    private $dbh = null;
    
    /**
     * Class constructor.
     */
    public function __construct()
    {
        $config = new \Config\DatabaseConfig();
        try
        {
            $connexionString = sprintf(self::CONNECTION_STRING_FORMAT, 
                $config->driver, $config->host, $config->dbname, $config->charset);
            $options = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            );
            $this->dbh = new PDO($connexionString, $config->username, 
                    $config->password, $options);
            parent::__construct();
        }
        catch (\Exception $ex)
        {
            die("Error connecting to the database" . $ex->getMessage());
        }
    }
    
    
    
    /**
     * Static method used to obtain the singleton instance of this class.
     * @return Singleton Singleton instance.
     */
    public static function getInstance() : \Core\Structures\Singleton
    {
        if (null === self::$instance) 
        {
            self::$instance = new ConnectionManager();
        }
        return self::$instance;
    }
    
    /**
     * Runs a query builder on the database connection.
     * @param \Addons\Database\QueryBuilder $builder Builder containing a valid query, 
     * will not validate the query.
     * @return bool Return the result of the query execution.
     */
    public function runQuery(QueryBuilder $builder) : bool
    {
        $datafunctions = Container::getInstance()
                ->set("\Addons\Database\DatabaseFunctions")->create($this->dbh);
        return $datafunctions->run($builder->get(), $builder->getStatmentParams());
    }
    
    /**
     * Runs a query builder and fetch the result of it execution on the database.
     * @param \Addons\Database\QueryBuilder $builder Builder containing a valid query, 
     * will not validate the query.
     * @param bool $Store Is the query cachable? If true, it will be cached for 
     * future use. False by default.
     * @return \Core\Structures\QueryableArray Returns a queryable array 
     * containing all the results from the database.
     */
    public function fetchQuery(QueryBuilder $builder, bool $Store = false) : \Core\Structures\QueryableArray
    {
        $helper = new SessionStoreHelper();
        //If the store already has this query stored
        if ($Store && $helper->InSessionStore(__FUNCTION__, $builder))
        {
            //Return the store rather than run the query
            return Container::getInstance()
                    ->set("\Core\Structures\QueryableArray")
                    ->create($helper->getSessionStore
                            (__FUNCTION__, $builder));
        }
        $datafunctions = Container::getInstance()
                ->set("\Addons\Database\DatabaseFunctions")->create($this->dbh);
        $result = $datafunctions->fetch($builder->get(), $builder->getStatmentParams());
        $helper->setStore($result, __FUNCTION__, $builder);
        return Container::getInstance()->set("\Core\Structures\QueryableArray")->create($result);
    }
    
    /**
     * Runs a query builder and fetch the results of its execution as STD classes.
     * @param \Addons\Database\QueryBuilder $builder Builder containing a valid query, 
     * will not validate the query.
     * @param bool $Store Is the query cachable? If true, it will be cached for 
     * future use. False by default.
     * @return \Core\Structures\QueryableArray Returns a queryable array 
     * containing all the results from the database.
     */
    public function fetchQueryObject(QueryBuilder $builder, bool $Store = false) : \Core\Structures\QueryableArray
    {
        $helper = new SessionStoreHelper();
        //If the store already has this query stored
        if ($Store && $helper->InSessionStore(__FUNCTION__, $builder))
        {
            //Return the store rather than run the query
            return Container::getInstance()
                    ->set("\Core\Structures\QueryableArray")
                    ->create($helper->getSessionStore
                            (__FUNCTION__, $builder));
        }
        $datafunctions = Container::getInstance()
                ->set("\Addons\Database\DatabaseFunctions")->create($this->dbh);
        $result = $datafunctions->fetchObject($builder->get(), $builder->getStatmentParams()); 
        $helper->setStore($result, __FUNCTION__, $builder);
        return Container::getInstance()->set("\Core\Structures\QueryableArray")->create($result);
    }
    
    /**
     * fetch all rows from the given table and selects the given columns.
     * @param string $table Table to select from in the database.
     * @param array $columns Columns to select, will not select all, always specify
     * which column to select.
     * @param array $orderBy Order by condition as an array of of the column to
     * order from as its key  and its direction (ASC or DESC) as its value.
     * @param bool $Store Is the query cachable? If true, it will be cached for 
     * future use. False by default.
     * @return \Core\Structures\QueryableArray Returns a queryable array 
     * containing all the results from the database.
     */
    public function fetchAllRows(string $table, array $columns, $orderBy = null, 
            bool $Store = false) : \Core\Structures\QueryableArray
    {
        $helper = new SessionStoreHelper();
        //If the store already has this query stored
        if ($Store && $helper->InSessionStore(__FUNCTION__, $table, $columns, $orderBy))
        {
            //Return the store rather than run the query
            return Container::getInstance()
                    ->set("\Core\Structures\QueryableArray")
                    ->create($helper->getSessionStore
                            (__FUNCTION__, $table, $columns, $orderBy));
        }
        $builder = Container::getInstance()
                ->set("\Addons\Database\QueryBuilder")->create();
        $builder->select($columns)->from(array($table));
        if (isset($orderBy))
        {
            $builder->orderBy($orderBy);
        }
        $datafunctions = Container::getInstance()
                ->set("\Addons\Database\DatabaseFunctions")->create($this->dbh);
        $result = $datafunctions->fetch($builder->get(), $builder->getStatmentParams());
        $helper->setStore($result, __FUNCTION__, $table, $columns, $orderBy);
        return Container::getInstance()->set("\Core\Structures\QueryableArray")->create($result);
    }
    
    /**
     * Ftech some rows from the given table with the given columns and filter 
     * the data with the Where clause.
     * @param string $table Table to select from in the database.
     * @param array $columns Columns to select, will not select all, always specify
     * which column to select.
     * @param string $Where Valid where conditional as a php condition string.
     * @param array $orderBy Order by condition as an array of of the column to
     * order from as its key  and its direction (ASC or DESC) as its value.
     * @param bool $Store Is the query cachable? If true, it will be cached for 
     * future use. False by default.
     * @return \Core\Structures\QueryableArray Returns a queryable array 
     * containing all the results from the database.
     */
    public function fetchRows(string $table, array $columns, string $Where, $orderBy = null, 
            bool $Store = false) : \Core\Structures\QueryableArray
    {
        $helper = new SessionStoreHelper();
        //If the store already has this query stored
        if ($Store && $helper->InSessionStore(__FUNCTION__, $table, $Where, $columns, $orderBy))
        {
            //Return the store rather than run the query
            return Container::getInstance()
                    ->set("\Core\Structures\QueryableArray")
                    ->create($helper->getSessionStore
                            (__FUNCTION__, $table, $Where, $columns, $orderBy));
        }
        $builder = Container::getInstance()
                ->set("\Addons\Database\QueryBuilder")->create();
        $builder->select($columns)->from($table)->where($Where);
        if (isset($orderBy))
        {
            $builder->orderBy($orderBy);
        }
        $datafunctions = Container::getInstance()
                ->set("\Addons\Database\DatabaseFunctions")->create($this->dbh);
        $result = $datafunctions->fetch($builder->get(), $builder->getStatmentParams());
        $helper->setStore($result, __FUNCTION__, $table, $Where, $columns, $orderBy);
        return Container::getInstance()->set("\Core\Structures\QueryableArray")->create($result);
    }
    
    /**
     * Insert a dataset inside the specified table with the given values.
     * @param string $table Name of the table to inset into.
     * @param array $values Array of values, can be either a numeric array with
     * only values or an associative array with the columns names as keys.
     * @return bool Returns the result of the query.
     */
    public function insert(string $table, array $values) : bool
    {
        $builder = Container::getInstance()
                ->set("\Addons\Database\QueryBuilder")->create();
        $builder->insert($table, $values);
        $datafunctions = Container::getInstance()
                ->set("\Addons\Database\DatabaseFunctions")->create($this->dbh);
        return $datafunctions->run($builder->get(), $builder->getStatmentParams());
    }
    
    /**
     * Update the selected rows in the given table. If no where clause is given, 
     * the system will update all rows with the new values.
     * @param string $table Name of the table to update.
     * @param array $values Associative array of values, must contain the 
     * column name as key and the values as values.
     * @param string $Where Php conditional in a string to select which rows to update, 
     * null by default.
     * @return bool Returns the result of the query.
     */
    public function update(string $table, array $values, $Where = null) : bool
    {
        $builder = Container::getInstance()
                ->set("\Addons\Database\QueryBuilder")->create();
        $builder->update($table, $values);
        if (isset($Where))
        {
            $builder->where($Where);
        }
        $datafunctions = Container::getInstance()
                ->set("\Addons\Database\DatabaseFunctions")->create($this->dbh);
        return $datafunctions->run($builder->get(), $builder->getStatmentParams());
    }
    
    /**
     * Delete rows in the database for the given table. If no where clause is given,
     * the system will delete all rows in the table.
     * @param string $table Name of the table to delete from.
     * @param type $Where Php conditional in a string to select which rows to update, 
     * null by default.
     * @return bool Returns the result of the query.
     */
    public function delete(string $table, $Where = null) : bool
    {
        $builder = Container::getInstance()
                ->set("\Addons\Database\QueryBuilder")->create();
        $builder->delete()->from($table);
        if (isset($Where))
        {
            $builder->where($Where);
        }
        $datafunctions = Container::getInstance()
                ->set("\Addons\Database\DatabaseFunctions")->create($this->dbh);
        return $datafunctions->run($builder->get(), $builder->getStatmentParams());
    }
}
