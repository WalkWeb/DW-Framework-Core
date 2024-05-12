<?php

use Tests\utils\ExampleHandler;

$routes = new WalkWeb\NW\Route\RouteCollection();

$routes->get('home', '/', ExampleHandler::class);

return new WalkWeb\NW\Route\Router($routes);
