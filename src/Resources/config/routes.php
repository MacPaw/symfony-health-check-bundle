<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use SymfonyHealthCheckBundle\Controller\HealthController;
use SymfonyHealthCheckBundle\Controller\PingController;

return function (RoutingConfigurator $routes): void {
    $routes->add('health', '/health')
        ->controller([HealthController::class, 'check'])
        ->methods(['GET']);

    $routes->add('ping', '/ping')
        ->controller([PingController::class, 'check'])
        ->methods(['GET']);
};
