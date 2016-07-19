<?php

namespace Addons\Templates\Html;

/**
 * This interface is implemented in all HTMl nodes and define the basic methods
 * all should inherit.
 */
interface HtmlNode
{
    /**
     * Return the current structure of the node and all of its children.
     * @return string HTMl structure.
     */
    public function toHtml() : string;
    
    /**
     * Transform the HTMl structure into PHP script according to the child's 
     * behavior.
     * @param array $data The data given by the model, used for transformable nodes.
     * @return string PHP script structure.
     */
    public function toScript(array $data = array()) : string;
}
