<?php

$routes = new NW\Route\RouteCollection();

$routes->get('home', '/', 'ExampleHandler');

return new NW\Route\Router($routes);
