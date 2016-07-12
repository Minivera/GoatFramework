<?php

namespace Core\HelperClasses;

/**
 * Class that allows the transformation of data in valid  and standardized
 * JSON syntax
 */
class JsonHelper
{
    /**
     * Prevent the class constructor. Static class.
     */
    private function __construct(){}
    
    /**
     * Static method which transform the result of a request into JSON data.
     * 
     * All Json object are made of two parameters, the status and the message. The
     * status is a boolean stating the quality of the request. If it worked, 
     * it's true, otherwise, it's false. The message can be anything the page will
     * receive.
     * @param bool $status Request status.
     * @param mixed $message Can contain anything, the javascript will receive 
     * this data and do what it needs with it.
     * @return string Valid Json text.
     */
    public static function jsonify(string $status, $message) : string
    {
        return json_encode(array("status" => $status, "message" => $message));
    }
}
