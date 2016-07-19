<?php

namespace Addons\Database;

/**
 * Aspected class containing function to access the database with the given PDO 
 * object. This class is not static, it is called to allow the aspect oriented
 * programming for transactions and logging.
 */
class DatabaseFunctions implements \Core\Aspect\Aspect
{
    /**
     * PDO instance given by this class' caller.
     * @var \PDO 
     */
    private $dbh;
    
    public function __construct(\PDO $dbh)
    {
        $this->dbh = $dbh;
    }
    
    /**
     * Registering function for the aspect Joinpoints. 
     * @param \DatabaseORM\AspectClasses\AspectManager $aspectManager The aspect 
     * manager to register to.
     */
    public function registerJoinPoints(\Core\Aspect\AspectManager &$aspectManager)
    {
        $aspectManager->registerBefore("Addons\Database\DatabaseFunctions->run()"
                , '$this->createTransaction');
        $aspectManager->registerAfter("Addons\Database\DatabaseFunctions->run()"
                , '$this->saveTransaction');
        $aspectManager->registerThrow("Addons\Database\DatabaseFunctions->run()"
                , '$this->cancelTransaction');
    }
    
    /**
     * Run the query with the given parameters and returns the query's result.
     * @param string $query SQL query to run on the database.
     * @param array $parameters Parameters to bind to the query, if any.
     * @return bool True if the execution worked, false if an error occurred.
     */
    public function run(string $query, array $parameters) : bool
    {
        $statement = $this->dbh->prepare($query);
        return $statement->execute($parameters);
    }
    
    /**
     * Run the query and fetch all the rows as a standard array
     * @param string $query SQL query to run on the database.
     * @param array $parameters Parameters to bind to the query, if any.
     * @return array Array of rows with columns in the order they are given in the database.
     */
    public function fetch(string $query, array $parameters) : array
    {
        $statement = $this->dbh->prepare($query);
        $statement->execute($parameters);
        return $statement->fetchAll();
    }
    
    /**
     * Run the query and fetch all rows as objects of the given class.
     * @param string $query SQL query to run on the database.
     * @param array $parameters Parameters to bind to the query, if any.
     * @param string $class Class name to create, will create a STDclass if no class name
     * is given.
     * @return array Array of object for the table.
     */
    public function fetchObject(string $query, array $parameters, string $class = "stdClass") : array
    {
        $statement = $this->dbh->prepare($query);
        $statement->execute($parameters);
        return $statement->fetchAll(PDO::FETCH_CLASS, $class);
    }
    
    /**
     * Aspected Before Advice, create a PDO object and start a transaction.
     */
    public function createTransaction()
    {
        $this->dbh->beginTransaction();
    }
    
    /**
     * Aspected After Advice, obtain the PDO object form the before global and 
     * commit the transaction.
     */
    public function saveTransaction()
    {
        $this->dbh->commit();
    }
    
    /**
     * Aspected Throw Advice, obtain the PDO object form the before global and 
     * rollback the transaction to prevent breaking the database with bad data.
     * @param \Exception $e Lifted Exception by the AfterThrow Advice.
     * @throws \Throwable
     */
    public function cancelTransaction(\Throwable $e)
    {
        if (isset($this->dbh))
        {
            $this->dbh->rollBack();
        }
        throw $e;
    }
}
