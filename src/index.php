<?php
/**
* Index file where all request are processed
**/

require_once "Autoloader.php";

//TODO: make a file for these four lines.
header ("Cache-Control: max-age=200 ");

ini_set('display_errors', 1);
ini_set('default_charset', 'utf-8');

session_start();

//Register the autoloader.
$classLoader = new \Autoloader();
$classLoader->register();

//Get the dependencyInjector instance.
$container = Core\Engines\DependencyEngine::getInstance();

//Build the final Exception Handler.
$container->set("\Core\Exceptions\ExceptionsHandler")->create();

//Create and show the page.
$frontController = $container->set("\Core\MVC\FrontController")->create();
echo $frontController->run();
