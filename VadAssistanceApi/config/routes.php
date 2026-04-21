<?php
/**
 * Routes configuration.
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->setExtensions(['json']);

        $builder->post('/support-request-contacts/{id}/status', ['controller' => 'SupportRequestContacts', 'action' => 'updateStatus'])->setPass(['id']);
        $builder->fallbacks();
    });
};
