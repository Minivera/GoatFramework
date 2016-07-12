<?php

namespace Models\Error;

/**
 * Description of ShowErrorModel
 */
class ShowErrorModel extends \Core\MVC\CoreModel
{
    public function __construct()
    {
        //TODO: Find a better way to move the exception data
        $exception = $_SESSION["SAVED_EXCEPTION"];
        unset($_SESSION["SAVED_EXCEPTION"]);
        $this->data = "<h1>Error " . $exception->getCode() . "</h1>\n" . 
                $exception->getMessage();
        parent::__construct();
    }
}
