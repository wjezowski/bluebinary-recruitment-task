<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->post('/api/coasters', [\App\Controllers\Api\Coasters::class, 'post']);
$routes->put('/api/coasters/(:segment)', [\App\Controllers\Api\Coasters::class, 'put']);