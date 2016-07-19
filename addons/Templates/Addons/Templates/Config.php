<?php

namespace Addons\Templates;

/**
 * Static config and constants class for the template addon
 */
class Config
{
    const DOC_CONFIG_NODE_TYPE = "goat-documentconfig";
    
    /**
     * Currently accepted HTMl custom tag by the HTMl template engine.
     */
    const TRANSFORMABLE_NODE_TYPE = array("goat-variable", "goat-if", 
        "goat-while", "goat-for");
    
    /**
     * Node type for a sub document to load as an HTMlDocument object.
     */
    const SUB_DOCUMENT_NODE_TYPE = "goat-subdocument";
    
    /**
     * Attribute defining the path to the sub-document to load.
     */
    const SUB_DOCUMENT_PATH = "path";
}
