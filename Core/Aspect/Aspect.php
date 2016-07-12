<?php

namespace Core\Aspect;

/**
 * Interface that is used to allow another class to be part of the AOP 
 * framework. When extending this class, the object will now be considered aspected
 * and will be managed by its aspect manager.
 *
 * @author gst-pierre
 */
interface Aspect
{
    /**
     * Method that force the child class to register a Join Point.
     * @param \DatabaseORM\AspectClasses\AspectManager $aspectManager Reference to the class' aspect manager.
     */
    function registerJoinPoints(\Core\Aspect\AspectManager &$aspectManager);
}
