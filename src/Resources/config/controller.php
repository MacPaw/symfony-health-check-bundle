<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SymfonyHealthCheckBundle\Controller\HealthController;
use SymfonyHealthCheckBundle\Controller\PingController;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->defaults()
        ->autowire()
        ->autoconfigure();

    $services->set(HealthController::class, HealthController::class)
        ->public()
        ->tag('container.service_subscriber');

    $services->set(PingController::class, PingController::class)
        ->public()
        ->tag('container.service_subscriber');
};
