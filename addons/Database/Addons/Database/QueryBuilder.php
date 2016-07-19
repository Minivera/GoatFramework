<?php

namespace Addons\Database;

/**
 * Class that allows the building of an advanced sql Query which can be sent
 * to any PDO drivers.
 */
class QueryBuilder implements SqlQueryable
{
    use OrderingChecker;
    
    /**
     * Represents the currently loaded query in the query builder, readonly.
     * @var string The well formed SQL query. 
     */
    private $query = "";
    
    /**
     * Associative array containing the variables in the SQL statement to be 
     * sent to PDO. The system uses numeric variables for its queries rather than
     * named variables.
     * @var array Array of variables, should never be sorted or weird things will happen.
     */
    private $statmentVariables = array();
    
    /**
     * Return the well formed query from the builder.
     * @return string SQL query.
     */
    public function get() : string
    {
        return rtrim($this->query) . ";";
    }
    
    /**
     * Returns the array of statement parameters for the PDO query.
     * @return array Array of unamed statement variables.
     */
    public function getStatmentParams() : array
    {
        return $this->statmentVariables;
    }
    
    /**
     * Start a select query by identifying the columns of each table and giving them
     * optional aliases. if called after an insert, it will act as a INSERT INTO SELECT.
     * @param array $Columns The columns to select in the table(s). If using 
     * multiple table, identify the table using "table.column".
     * @param array $aliases Associative array of column name => alias. It allows 
     * the rename of each column name for easier management. Null by default.
     */
    public function select(array $Columns, array $aliases = array()) : SqlQueryable
    {
        $columnsNames = array();
        //Build the column names.
        foreach ($Columns as $value)
        {
            if (key_exists($value, $aliases))
            {
                array_push($columnsNames, "$value AS $aliases[$value]");
            }
            else
            {
                array_push($columnsNames, $value);
            }
        }
        $this->query .= "SELECT " . implode(", ", $columnsNames) . " ";
        $this->registerCall(__FUNCTION__);
        return $this;
    }
    
    /**
     * Start an insert query by identifying which values to insert into which 
     * table. If called after a select, it will act as a SELECT INTO.
     * @params string $table The name of the table to insert into.
     * @params array $values values to insert in the table. If associative,
     * the system will use the keys as column names for the insert.
     */
    public function insert(string $table, array $values) : SqlQueryable
    {
        $Query = "INSERT INTO $table ";
        $columns = array();
        $inserts = array();
        foreach ($values as $key => $value)
        {
            //If the array is not associative
            if (is_int($key))
            {
                array_push($this->statmentVariables, $value);
                array_push($inserts, "?");
            }
            else
            {
                array_push($columns, $key);
                array_push($this->statmentVariables, $value);
                array_push($inserts, "?");
            }
        }
        if (!empty($columns))
        {
            $Query .= "(" . implode(", ", $columns) . ") ";
        }
        $Query .= "VALUES (" . implode(", ", $inserts) . ") ";
        $this->query .= $Query;
        $this->registerCall(__FUNCTION__);
        return $this;
    }
    
    /**
     * Start an update query by identifying which values to update into which 
     * table.
     * @params string $table The name of the table to update.
     * @params array $values values to update in an associative array with the 
     * keys as the columns name.
     */
    public function update(string $table, array $values) : SqlQueryable
    {
        $updates = array();
        foreach ($values as $key => $value)
        {
            array_push($updates, "$key=?");
            array_push($this->statmentVariables, $value);
        }
        $this->query .= "UPDATE $table SET " . implode(", ", $updates) . " ";
        $this->registerCall(__FUNCTION__);
        return $this;
    }
    
    /**
     * Start a delete Query, empty until it is given a from or where query.
     */
    public function delete() : SqlQueryable
    {
        $this->query .= "DELETE ";
        $this->registerCall(__FUNCTION__);
        return $this;
    }
    
    /**
     * Next step of select query that allows to identify which table to select 
     * from. Must be called AFTER a select or a delete and a FROM.
     * @param string|array $tables Array containing which tables to select from.
     * @param array $aliases Associative array of table name => alias. It allows 
     * the rename of each table for easier management. Null by default.
     * @throws \BadMethodCallException
     */
    public function from($tables, array $aliases = array()) : SqlQueryable
    {
        if (!$this->assertOrder("select", "delete"))
        {
            throw new \BadMethodCallException("The method from must be called after a SELECT or a DELETE");
        }
        if (is_string($tables))
        {
            $tables = array($tables);
        }
        if (is_array($tables))
        {
            $tablenames = array();
            foreach ($tables as $value)
            {
                if (key_exists($value, $aliases))
                {
                    array_push($tablenames, "$value AS $aliases[$value]");
                }
                else
                {
                    array_push($tablenames, $value);
                }
            }
            $this->query .= "FROM " . implode(", ", $tablenames) . " ";
        }
        $this->registerCall(__FUNCTION__);
        return $this;
    }
    
    /**
     * Allow a condition on the select query, write the conditional in SQL or 
     * PHP syntax. Must be called after a query starter.
     * @param string $condition the condition to check in the query.
     * @throws \BadMethodCallException
     * TODO: Allow the addition of prepared statement variables.
     */
    public function where(string $condition) : SqlQueryable
    {
        if (!$this->assertOrder("select", "delete", "insert", "update") 
                && !$this->assertPrecedent("from"))
        {
            throw new \BadMethodCallException("The method where must be called "
                    . "after a FROM and must come after a query starter.");
        }
        $condition = str_replace("&&", "AND", $condition);
        $condition = str_replace("||", "OR", $condition);
        $this->query .= "WHERE $condition ";
        $this->registerCall(__FUNCTION__);
        return $this;
    }
    
    /**
     * Allow the ordering of the dataset according to the given ordering. 
     * Must be called AFTER a select.
     * @param array $ordering Desired ordering of the columns. Associative array
     * of column name => Ordering (Either ASC or DESC).
     * @throws \BadMethodCallException
     */
    public function orderBy(array $ordering) : SqlQueryable
    {
        if (!$this->assertOrder("select"))
        {
            throw new \BadMethodCallException("The method orderBy must be called "
                    . "after a SELECT.");
        }
        $orderBy = array();
        foreach ($ordering as $key => $value)
        {
            array_push($orderBy, "$key $value");
        }
        $this->query .= "ORDER BY " . implode(", ", $orderBy) . " ";
        $this->registerCall(__FUNCTION__);
        return $this;
    }
    
    /**
     * Starts a Inner join process by giving the join table name and the condition
     * for the join. See the where function documentation on how to format a condition.
     * Must be called AFTER a select.
     * @param string $table table on which to join, use aliases if aliases were defined.
     * @param string $on The on condition for the join.
     * @throws \BadMethodCallException
     * TODO: Allow the addition of prepared statement variables.
     */
    public function innerJoin(string $table, string $on) : SqlQueryable
    {
        if (!$this->assertOrder("select"))
        {
            throw new \BadMethodCallException("The method innerJoin must be called "
                    . "after a SELECT");
        }
        $this->query .= "JOIN $table ON $on ";
        $this->registerCall(__FUNCTION__);
        return $this;
    }
    
    /**
     * Start a left join process that takes all columns from the selected table
     * and the matched from the on condition on the joined table. See the where 
     * function documentation on how to format a condition. Must be called AFTER a select.
     * @param string $table table on which to join, use aliases if aliases were defined.
     * @param string $on The on condition for the join.
     * @throws \BadMethodCallException
     * TODO: Allow the addition of prepared statement variables.
     */
    public function leftJoin(string $table, string $on) : SqlQueryable
    {
        if (!$this->assertOrder("select"))
        {
            throw new \BadMethodCallException("The method leftJoin must be called "
                    . "after a SELECT");
        }
        $this->query .= "LEFT JOIN $table ON $on ";
        $this->registerCall(__FUNCTION__);
        return $this;   
    }
    
    /**
     * Start a right join process that takes the matching column from the on condition 
     * on the selected table and all the columns from the joined table. See the where 
     * function documentation on how to format a condition. Must be called AFTER a select.
     * @param string $table table on which to join, use aliases if aliases were defined.
     * @param string $on The on condition for the join.
     * @throws \BadMethodCallException
     * TODO: Allow the addition of prepared statement variables.
     */
    public function rightJoin(string $table, string $on) : SqlQueryable
    {
        if (!$this->assertOrder("select"))
        {
            throw new \BadMethodCallException("The method rightJoin must be called "
                    . "after a SELECT");
        }
        $this->query .= "RIGHT JOIN $table ON $on ";
        $this->registerCall(__FUNCTION__);
        return $this;     
    }
    
    /**
     * Start a full join process that takes all columns from both tables joined 
     * by the on condition. See the where function documentation on how to format 
     * a condition. Must be called AFTER a select.
     * @param string $table table on which to join, use aliases if aliases were defined.
     * @param string $on The on condition for the join.
     * @throws \BadMethodCallException
     * TODO: Allow the addition of prepared statement variables.
     */
    public function fullJoin(string $table, string $on) : SqlQueryable
    {
        if (!$this->assertOrder("select"))
        {
            throw new \BadMethodCallException("The method fullJoin must be called "
                    . "after a SELECT");
        }
        $this->query .= "FULL OUTER JOIN $table ON $on ";
        $this->registerCall(__FUNCTION__);
        return $this; 
    }
    
    /**
     * Allow the start of a new select query and join the two results in a union
     * that combines the two results.
     * @throws \BadMethodCallException
     */
    public function union() : SqlQueryable
    {
        if (!$this->assertOrder("select"))
        {
            throw new \BadMethodCallException("The method union must be called "
                    . "after a SELECT");
        }
        $this->query .= "UNION ";
        $this->registerCall(__FUNCTION__);
        return $this;    
    }
}
