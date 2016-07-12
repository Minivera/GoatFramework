<?php

namespace Core\Engines;

use Core\Engines\DependencyEngine as Container;

/**
 * Class tasked with displaying the received data to the page. This is the step 
 * between the client page and the view needed for a few addons.
 */
class DisplayEngine
{
    /**
     * Display the view's data as a static page.
     * @param string $Route The route to the current MVC trio to send back data.
     * @param mixed $ModelData The model data to show.
     * @param \Exception $ModelException The optional lifted exception in the 
     * model to translate and show.
     */
    public function display(string $Route, $ModelData, \Throwable $ModelException = null)
    {
        $translator = Container::getInstance()->set("\Core\Engines\TranslationEngine")->create();
        //Display the route, the data and the exception.
        echo "<div id='ViewRoute'>$Route</div>";
        if (isset($ModelException))
        {
            echo $translator->translateException($ModelException);
        }
        echo $translator->translate($ModelData);
    }
}
