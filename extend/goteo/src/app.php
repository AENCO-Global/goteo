<?php

use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Goteo\Application\Config;


$custom_routes = new RouteCollection();
$custom_routes->add('barcelona-node', new Route(
    '/{url}',
    array(
        '_controller' => 'Goteo\Controller\NodeController::barcelonaAction',
        'url' => ''
        ),
    array(
        'url' => '.*',
         'domain' => 'barcelona.goteo.org|betabarcelona.goteo.org|devgoteo.org'
        ), // Para testeo, devgoteo.org sirve como nodo "barcelona"
    array(),
    '{domain}'
));

// Adding Default routes
$main_routes = include(__DIR__ . '/../../../src/app.php');
$custom_routes->addCollection($main_routes);

return $custom_routes;
