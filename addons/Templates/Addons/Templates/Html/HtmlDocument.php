<?php

namespace Addons\Templates\Html;

use Core\Engines\DependencyEngine as Container;

/**
 * Object structure for a HTMl document. The class loads a HTMl file and transforms it
 * in this structure for easy management.
 */
class HtmlDocument implements HtmlNode
{   
    /**
     * Array containing references to all the HtmlStdNode in the document.
     * @var array Array of HtmlStdNode objects. 
     */
    private $stdNodes = array();
    
    /**
     * Array containing references to all the TransformableHtmlNodes in the document.
     * @var array Array of TransformableHtmlNodes objects.
     */
    private $transformableNodes = array();
    
    /**
     * Array containing references to all HtmlDocument in the current HTMl document.
     * @var array Array of HtmlDocument objects. 
     */
    private $subDocuments = array();
    
    /**
     * Array containing all the node in the HTMl document in a tree structure.
     * @var array Array of HtmlNode objects. 
     */
    private $structure = array();
    
    /**
     * Define if the document should be a static page, if yes, it will 
     * never be regenerated. False by default.
     * @var bool 
     */
    private $isStatic = false;
    
    /**
     * Define if the page must be cache to speed up request, if yes, it will be 
     * regenerated according to the CacheTime. false by default.
     * @var bool
     */
    private $isCached = false;
    
    /**
     * Defines the time in seconds the page should be cached for. Default if 60 seconds.
     * @var int 
     */
    private $cacheTime = 60;
    
    /**
     * Class constructor, loads the HTML file as a structure of HTMl nodes.
     * @param string $path Path to the HTMl file
     */
    public function __construct(string $path)
    {
        //Try to load the file
        try
        {
            $document = new \DOMDocument();
            //Stupid custom tag errors
            libxml_use_internal_errors(true);
            $document->loadHTMLFile($path);
            //TODO: Switch from DOM to something else.
            libxml_clear_errors();
            $document->preserveWhiteSpace = false;
            //Run through all current nodes
            $roots = $document->childNodes;
            if (!empty($roots)) 
            {
                foreach ($roots as $root)
                {
                    if ($root->nodeType === XML_ELEMENT_NODE)
                    {   
                        $node = $this->buildNode($root);
                        //If there is child nodes, start 
                        if ($root->hasChildNodes())
                        {
                            $this->runThroughNode($root, $node);
                        }
                        array_push($this->structure, $node);
                    }
                }
            }
            else
            {
                //Throw empty document
            }
        }
        catch (\Exception $e)
        {
            //TODO : Error management
        }
    }
    
    /**
     * Recursive function that runs through the tree structure of the HTML 
     * document to loads the nodes.
     * @param \DOMNode $DOMnode Current DomDocument node in the recursive cycle.
     * @param \Addons\Templates\Html\HtmlNode $node Current custom node in the recursive cycle
     */
    private function runThroughNode(\DOMNode $DOMnode, HtmlNode $node)
    {
        //Loop through all nodes of this level
        foreach ($DOMnode->childNodes as $child) 
        {
            //Check if the root contains a document config node
            if ($child->nodeName === \Addons\Templates\Config::DOC_CONFIG_NODE_TYPE)
            {
                $this->registerConfig($child);
                continue;
            }
            //We only care about element nodes
            if ($child->nodeType === XML_ELEMENT_NODE)
            {   
                $ChildNode = $this->buildNode($child);
                //If there is child nodes, run the function again
                if ($child->hasChildNodes())
                {
                    $this->runThroughNode($child, $ChildNode);
                }
                $node->addChildNode($ChildNode);
            }
            else if ($child->nodeType === XML_TEXT_NODE)
            {
                $node->appendValue($child->wholeText);
            }
        }
    }
    
    /**
     * Build the right type of HTMl node instance to fit the type of node received.
     * @param \DOMElement $DOMnode Current DOM document node.
     * @return \Addons\Templates\Html\HtmlNode Returns an instance of the HTMLNode
     */
    private function buildNode(\DOMElement $DOMnode) : HtmlNode
    {
        //Check node name to see if it is a standard or transformable node
        if (in_array($DOMnode->nodeName, \Addons\Templates\Config::TRANSFORMABLE_NODE_TYPE))
        {
            $node = Container::getInstance()
                    ->set("\Addons\Templates\Html\HtmlTransformableNode")
                    ->create($DOMnode);
            array_push($this->transformableNodes, $node);
        }
        else if ($DOMnode->nodeName === \Addons\Templates\Config::SUB_DOCUMENT_NODE_TYPE)
        {
            $node = Container::getInstance()
                    ->set("\Addons\Templates\Html\HtmlDocument")
                    ->create($DOMnode->getAttribute(\Addons\Templates\Config::SUB_DOCUMENT_PATH));
            array_push($this->subDocuments, $node);
        }
        else
        {
            $node = Container::getInstance()
                    ->set("\Addons\Templates\Html\HtmlStdNode")
                    ->create($DOMnode);
            array_push($this->stdNodes, $node);
        }
        return $node;
    }
    
    /**
     * register the given documentconfig node into this document.
     * @param \DOMElement $DOMnode The node, should be a document config node.
     */
    private function registerConfig(\DOMElement $DOMnode)
    {
        //Try to extract the attributes list for the DocumentConfig
        //TODO : Make this dynamic
        if ($DOMnode->hasAttribute("static"))
        {
            //If the page is static, we don't care about caching.
            $this->isStatic = true;
            return;
        }
        if ($DOMnode->hasAttribute("cache"))
        {
            $this->isCached = true;
            if ($DOMnode->hasAttribute("cache-lenght"))
            {
                $this->cacheTime = $DOMnode->getAttribute("cache-lenght");
            }
        }
    }
    
    /**
     * Write the Document and all its node as HTML
     */
    public function toHtml() : string
    {
        $html = "";
        foreach ($this->structure as $value)
        {
            $html .= $value->toHtml();
        }
        return $html;
    }

    /**
     * Translate the Document and all its node as a special PHP script.
     */
    public function toScript(array $data = array()) : string
    {
        $html = "";
        foreach ($this->structure as $value)
        {
            $html .= $value->toScript($data);
        }
        return $html;
    }
    
    /**
     * Get the isStatic readonly attribute.
     * @return bool Identify if the document is a static document (Never generated)
     */
    public function getIsStatic() : bool
    {
        return $this->isStatic;
    }
    
    /**
     * get the IsCached readonly attribute.
     * @return bool identify the document as cached.
     */
    public function getIsCached() : bool
    {
        return $this->isCached;
    }
    
    /**
     * Get the cache length in seconds.
     * @return int get the number of seconds a page must be cached.
     */
    public function getCacheLenght() : int
    {
        return $this->cacheTime;
    }
    
    /**
     * Public function used to start the script generation of the document.
     * @param array $data Data given by the model to send to the transformables nodes.
     * @return string Return the script structure with all the information.
     */
    public function generateScript(array $data) : string
    {
        return $this->toScript($data);
    }
}
