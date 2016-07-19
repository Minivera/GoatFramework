<?php

namespace Addons\Templates\Html;

/**
 * Standard HTMl node, this node requires no further processing.
 */
class HtmlStdNode implements HtmlNode
{
    /**
     * Name of the node's HTMl tag.
     * @var string 
     */
    protected $tagName = "";
    
    /**
     * Text inside of the node, it will be appended before the other child nodes.
     * @var string 
     */
    protected $value = "";
    
    /**
     * Array of attributes in the node.
     * @var array Associative array of attributes with the attribute name as 
     * the key. 
     */
    protected $attributes = array();
    
    /**
     * Array of child node for this node.
     * @var array Array of HtmlNodes. 
     */
    protected $childNodes = array();
    
    /**
     * Class constructor
     * @param \DOMElement $DOMnode Dom element in the Dom document used to 
     * create the node.
     */
    public function __construct(\DOMElement $DOMnode)
    {
        $this->tagName = $DOMnode->nodeName;
        for ($i = 0; $i < $DOMnode->attributes->length; $i++)
        {
            $this->attributes[$DOMnode->attributes->item($i)->name] = 
                    $DOMnode->attributes->item($i)->value;
        }
    }
    
    /**
     * Set the value for the node and returns the current node instance for 
     * function chaining.
     * @param string $value Value to set for the node.
     * @return \Addons\Templates\Html\HtmlNode Returns the current node instance.
     */
    public function setValue(string $value) : HtmlNode
    {
        $this->value = $value;
        return $this;
    }
    
    /**
     * Get the value of the node.
     * @return string The node's value.
     */
    public function getValue() : string
    {
        return $this->value;
    }
    
    /**
     * Append the given text to the current node's value and returns the 
     * current node instance for function chaining.
     * @param string $value value to append.
     * @return \Addons\Templates\Html\HtmlNode Returns the current node instance.
     */
    public function appendValue(string $value) : HtmlNode
    {
        $this->value .= $value;
        return $this;
    }
    
    /**
     * Add a child node to the end of the child node array and returns the 
     * current node instance for function chaining.
     * @param \Addons\Templates\Html\HtmlNode $ChildNode Child node to add.
     * @return \Addons\Templates\Html\HtmlNode Returns the current node instance.
     */
    public function addChildNode(HtmlNode $ChildNode) : HtmlNode
    {
        array_push($this->childNodes, $ChildNode);
        return $this;
    }
    
    /**
     * Get the child node at the specified index.
     * @param int $index Index of the wanted child node.
     * @return \Addons\Templates\Html\HtmlNode|null Instance of the obtained Childnode 
     * or null if it doesn't exists.
     */
    public function getChildNode(int $index)
    {
        if (key_exists($index, $this->childNodes))
        {
            return $this->childNodes[$index];
        }
        return null;
    }
    
    /**
     * Get the array of child nodes.
     * @return array Array containing all the childnodes, empty if there is no 
     * childnodes.
     */
    public function getChildNodes() : array
    {
        return $this->childNodes;
    }
    
    /**
     * Remove the childnode for the given index and returns the 
     * current node instance for function chaining.
     * @param int $index Index of the childnode to delete.
     * @return \Addons\Templates\Html\HtmlNode Returns the current instance.
     */
    public function removeChildNode(int $index) : HtmlNode
    {
        unset($this->childNodes[$index]);
        return $this;
    }
    
    /**
     * Set or add on attribute for the node and returns the 
     * current node instance for function chaining.
     * @param string $name The name of the attribute.
     * @param string $value The value of the attribute.
     * @return \Addons\Templates\Html\HtmlNode Returns the current instance.
     */
    public function setAttribute(string $name, string $value) : HtmlNode
    {
        $this->attributes[$name] = $value;
        return $this;
    }
    
    /**
     * Get the attribute identified by it's name if it exists.
     * @param string $name The name of the attribute.
     * @return string|null Returns the attribute value if it exists, null if not.
     */
    public function getAttribute(string $name)
    {
        if (key_exists($name, $this->attributes))
        {
            return $this->attributes[$name];
        }
        return null;
    }
    
    /**
     * Remove the attribute given from the attribute list and returns the 
     * current node instance for function chaining.
     * @param string $name The attribute name.
     * @return \Addons\Templates\Html\HtmlNode Returns the current instance.
     */
    public function removeAttribute(string $name) : HtmlNode
    {
        unset($this->attributes[$name]);
        return $this;
    }
    
    /**
     * Return the HTMl structure of the node and all its children.
     * @return string Html Structure
     */
    public function toHtml() : string
    {
        $html = "<$this->tagName" . $this->attributeToString() . ">$this->value";
        foreach ($this->childNodes as $value)
        {
            $html .= $value->toHtml();
        }
        return $html . "</$this->tagName>";
    }

    /**
     * Write the Php script structure of the node and all its children to the file writer.
     * @return string Php script structure.
     */
    public function toScript(array $data = array()) : string
    {
        $html = "<$this->tagName" . $this->attributeToString() . ">$this->value";
        foreach ($this->childNodes as $value)
        {
            $html .= $value->toScript($data);
        }
        return $html . "</$this->tagName>";
    }

    /**
     * Turns the attribute array to a string that can be added to an HTML node.
     * @return string The attribute string formatted for HTML.
     */
    protected function attributeToString() : string
    {
        $string = "";
        foreach ($this->attributes as $key => $value)
        {
            $string .= " $key='$value'";
        }
        return $string;
    }
}
