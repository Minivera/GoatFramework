<?php

namespace Core\MVC;

use Core\Engines\DependencyEngine as Container;

/**
 * Abstract generic class for the controllers. It defines the basic behavior for 
 * controllers and the aspect rule for logging.
 *
 * @abstract
 */
abstract class CoreController implements \Core\Aspect\Aspect
{
    /**
     * Model associated with this controller, all calls can be sent 
     * directly to this model.
     * @var \Core\MVC\CoreModel Instance of the controller's associated model.
     */
    protected $Model;
    
    /**
     * Class constructor.
     * @param \Core\MVC\CoreModel $model Object model to associate to the controller.
     */
    public function __construct(\Core\Aspect\AspectManager $model)
    {
        $this->Model = $model;
    }
    
    /**
     * Registering function for the aspect Joinpoints. 
     * @param \Core\Aspect\AspectManager $aspectManager The aspect 
     * manager to register to.
     */
    public function registerJoinPoints(\Core\Aspect\AspectManager &$aspectManager)
    {
        $aspectManager->registerThrow("Core\MVC\CoreController->*()"
                , 'Core\Exception\Logger->logException()');
    }
}
