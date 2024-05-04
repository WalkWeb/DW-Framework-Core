<?php

$routes = new WalkWeb\NW\Route\RouteCollection();

$routes->get('home', '/', 'ExampleHandler');

return new WalkWeb\NW\Route\Router($routes);
