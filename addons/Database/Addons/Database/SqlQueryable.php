<?php

namespace Addons\Database;

/**
 * Sql based implementation of the queryable interface from core, it renames and 
 * removes some methods to alow the same functionality, but with SQL rather than 
 * an array of objects. This should only be used for sending queries, manipulate
 * the data with a standard queryable once retrieved
 * 
 * This interface, for obvious reasons, does not support callables as the normal
 * queryable does.
 */
interface SqlQueryable
{
    /**
     * Start a select query by identifying the columns of each table and giving them
     * optional aliases. if called after an insert, it will act as a INSERT INTO SELECT.
     * @param array $Columns The columns to select in the table(s). If using 
     * multiple table, identify the table using "table.column".
     * @param array $aliases Associative array of column name => alias. It allows 
     * the rename of each column name for easier management. Null by default.
     */
    public function select(array $Columns, array $aliases = array()) : SqlQueryable;
    
    /**
     * Start an insert query by identifying which values to insert into which 
     * table. If called after a select, it will act as a SELECT INTO.
     * @params string $table The name of the table to insert into.
     * @params array $values values to insert in the table. If associative,
     * the system will use the keys as column names for the insert.
     */
    public function insert(string $table, array $values) : SqlQueryable;
    
    /**
     * Start an update query by identifying which values to update into which 
     * table.
     * @params string $table The name of the table to update.
     * @params array $values values to update in an associative array with the 
     * keys as the columns name.
     */
    public function update(string $table, array $values) : SqlQueryable;
    
    /**
     * Start a delete Query, empty until it is given a from or where query.
     */
    public function delete() : SqlQueryable;
    
    /**
     * Next step of select query that allows to identify which table to select 
     * from. Must be called AFTER a select or a delete.
     * @param string|array $tables Array containing which tables to select from.
     * @param array $aliases Associative array of table name => alias. It allows 
     * the rename of each table for easier management. Null by default.
     */
    public function from($tables, array $aliases = null) : SqlQueryable;
    
    /**
     * Allow a condition on the select query, write the conditional in SQL or 
     * PHP syntax. Must be called a query starter.
     * @param string $condition the condition to check in the query.
     */
    public function where(string $condition) : SqlQueryable;
    
    /**
     * Allow the ordering of the dataset according to the given ordering. 
     * Must be called AFTER a select.
     * @param array $ordering Desired ordering of the columns. Associative array
     * of column name => Ordering (Either ASC or DESC).
     */
    public function orderBy(array $ordering) : SqlQueryable;
    
    /**
     * Starts a Inner join process by giving the join table name and the condition
     * for the join. See the where function documentation on how to format a condition.
     * Must be called AFTER a select.
     * @param string $table table on which to join, use aliases if aliases were defined.
     * @param string $on The on condition for the join.
     */
    public function innerJoin(string $table, string $on) : SqlQueryable;
    
    /**
     * Start a left join process that takes all columns from the selected table
     * and the matched from the on condition on the joined table. See the where 
     * function documentation on how to format a condition. Must be called AFTER a select.
     * @param string $table table on which to join, use aliases if aliases were defined.
     * @param string $on The on condition for the join.
     */
    public function leftJoin(string $table, string $on) : SqlQueryable;
    
    /**
     * Start a right join process that takes the matching column from the on condition 
     * on the selected table and all the columns from the joined table. See the where 
     * function documentation on how to format a condition. Must be called AFTER a select.
     * @param string $table table on which to join, use aliases if aliases were defined.
     * @param string $on The on condition for the join.
     */
    public function rightJoin(string $table, string $on) : SqlQueryable;
    
    /**
     * Start a full join process that takes all columns from both tables joined 
     * by the on condition. See the where function documentation on how to format 
     * a condition. Must be called AFTER a select.
     * @param string $table table on which to join, use aliases if aliases were defined.
     * @param string $on The on condition for the join.
     */
    public function fullJoin(string $table, string $on) : SqlQueryable;
    
    /**
     * Allow the start of a new select query and join the two results in a union
     * that combines the two results.
     */
    public function union() : SqlQueryable;
}
