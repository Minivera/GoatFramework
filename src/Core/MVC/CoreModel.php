<?php

namespace Core\MVC;

use Core\Engines\DependencyEngine as Container;

/**
 * Abstract generic class for the models. 
 *
 * @abstract
 */
abstract class CoreModel implements \Core\Aspect\Aspect
{
    /**
     * Basic model data, contains everything that should be displayed through the view. View only
     * @var object HTMl text or an object that can display HTML text. 
     */
    protected $data;
    
    /**
     * Exception object containing the info from the last lifted and caught 
     * exception in the model. It is used to display an error message using the 
     * exception translator. View only.
     * @var \Throwable Instance of the lifted exception. 
     */
    protected $exception = null;
    
    /**
     * Identify if the current request is an AJAX request or not. 
     * @var boolean true if an Ajax Request, false if not.
     */
    public $ajax = false;
    
    /**
     * Current request status. Identify if the request went as expected or 
     * if an exception was lifted.
     * @var boolean True if everything worked, false if not.
     */
    public $status = true;
    
    /**
     * Class constructor
     */
    public function __construct()
    {
    }
    
   /**
     * Registering function for the aspect Joinpoints. 
     * @param \Core\Aspect\AspectManager $aspectManager The aspect 
     * manager to register to.
     */
    public function registerJoinPoints(\Core\Aspect\AspectManager &$aspectManager)
    {
        $aspectManager->registerThrow("Core\MVC\CoreModel->*()"
                , '$this->manageException()');
    }
    
    /**
     * Manage any lifted exceptions in the class by adding the lifted exception 
     * to the class' internal exception and log it.
     * @param \Exception $ex
     */
    private function manageException(\Throwable $ex)
    {
        $this->exception = $ex;
        $logger = Container::getInstance()->set("\Core\Exceptions\Logger")->create();
        $logger->logException($ex);
    }
    
    /**
     * Get the current data in the model.
     * @return string HTML data contained in the model.
     */
    public function getData()
    {
        return $this->data;
    }
    /**
     * Return the last lifted exception in the model.
     * @return \Exception Instance of the lifted exception.
     */
    public function getException()
    {
        return $this->exception;
    }
}
