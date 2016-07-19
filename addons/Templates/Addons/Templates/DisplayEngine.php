<?php

namespace Addons\Templates;

use Core\Engines\DependencyEngine as Container;

/**
 * Override class for the core display engine. It loads the the template 
 * according to the route and send the view's data in it.
 */
class DisplayEngine
{
    /**
     * Display the view's data in the route,s template or as a static page if
     * no template is found.
     * @param string $Route The route to the current MVC trio to send back data.
     * @param array $ModelData The model data to show in an array.
     * @param \Exception $ModelException The optional lifted exception in the 
     * model to translate and show.
     */
    public function display(string $Route, array $ModelData, \Throwable $ModelException = null)
    {
        $template = Container::getInstance()->set("\Addons\Templates\TemplateClass")->create($Route);
        $template->generate($ModelData, $ModelException);
    }
}
