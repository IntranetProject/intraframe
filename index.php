<?php
/**
 * Created by PhpStorm.
 * User: bennet
 * Date: 05.07.18
 * Time: 22:44
 */

// This is just a test & example file.
include("vendor/autoload.php");

\Tracy\Debugger::enable();

define("MYSQL_HOST", "localhost");
define("MYSQL_USER", "root");
define("MYSQL_PASSWORD", "root");
define("MYSQL_DATABASE", "test");

$router = \intraframe\Router\Router::getInstance();

$router->setBasepath("/intraframe");

$router->get("/", new \intraframe\ViewModels\RootViewModel());
//$router->get('/not-found', new \intraframe\ViewModels\NotFoundViewModel());

include 'app/controller.php';