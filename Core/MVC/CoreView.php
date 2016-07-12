<?php

namespace Core\MVC;

use Core\Engines\DependencyEngine as Container;

/**
 * Abstract generic view class.
 * 
 * @abstract
 */
abstract class CoreView implements \Core\Aspect\Aspect
{
    /**
     * The view's associated model instance containing all data to display.
     * @var \Core\MVC\CoreModel Instance of a child object of the core Model.
     */
    protected $Model;
    
    /**
     * The view's route given by the front-controller, it allows all calls to 
     * be sent to the rght URL by the client scripts.
     * @var string Current URL routename.
     */
    protected $Route;
    
    /**
     * Display engine used to translate the data given by the model and 
     * formatted by the view back to the page. It ensures all data is valid HTMl
     * text and can translate non HTML data from the model into the page.
     * @var \Core\Engines\DisplayEngine Instance of the display engine. 
     */
    protected $DisplayEngine;
    
    /**
     * Class constructor.
     * @param string $Route Current view route to display in page.
     * @param \Core\MVC\CoreModel $Model Associated model instance to this view.
     */
    public function __construct(string $Route, \Core\Aspect\AspectManager $Model) 
    {
        $this->Route = $Route;
        $this->Model = $Model;
        $this->DisplayEngine = Container::getInstance()->set("\Core\Engines\DisplayEngine")->create();
    }
    
    /**
     * Outputs the model data through the display engine or directly if the 
     * request is identified as an Ajax request.
     */
    public function output() 
    {
        if ($this->Model->ajax)
        {
            $data = null !== $this->Model->getException() ? $this->Model->getException() : $this->Model->getData();
            echo \Core\HelperClasses\JsonHelper::jsonify($this->Model->Status, $data);
        }
        else
        {
            $this->DisplayEngine->display($this->Route, $this->Model->getData(), 
                    $this->Model->getException());
        }
    }
    
    /**
     * Empty register join points function.
     * @param \Core\Aspect\AspectManager $aspectManager The aspect manager to 
     * register the join point to.
     */
    public function registerJoinPoints(\Core\Aspect\AspectManager &$aspectManager)
    {
    }
}
