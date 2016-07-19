<?php

namespace Addons\Templates\Html;

/**
 * HTML special node that is interpreted by the system and transformed into a PHP
 * directive or a HTMl component. Extends the HTMl standard node.
 */
class HtmlTransformableNode extends HtmlStdNode
{
    public function __construct(\DOMElement $DOMnode)
    {
        parent::__construct($DOMnode);
        $this->checkValidity();
    }
    
    private function checkValidity()
    {
        //Both static and variable tag have the same attributes. 
        //Check if the name attribute exist, if not, throw an error.
        if (!key_exists("name", $this->attributes))
        {
            throw new \Error("The $this->tagName tag must have a name attribute.");
        }
    }
    
    /**
     * Write the Php script structure of the node and all its children.
     * @return string Php script structure.
     */
    public function toScript(array $data = array()) : string
    {
        return $this->getScriptTag($data);
    }
    
    /**
     * TODO: If, while and for transformable nodes.
     * Get the current node as a PHP script tag according to its type.
     * @return string The PHP structure of the tag.
     */
    private function getScriptTag(array $data = array()) : string
    {
        \extract($data);
        switch ($this->tagName)
        {
            case "goat-variable" :
            {
                $name = $this->attributes["name"];
                return $$name;
            }
            //In case this was wrongly created
            default : 
            {
                //TODO: Error management
            }
        }
    }
}
