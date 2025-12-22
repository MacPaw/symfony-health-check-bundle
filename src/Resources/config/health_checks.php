<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use SymfonyHealthCheckBundle\Adapter\RedisAdapterWrapper;
use SymfonyHealthCheckBundle\Check\DoctrineODMCheck;
use SymfonyHealthCheckBundle\Check\DoctrineORMCheck;
use SymfonyHealthCheckBundle\Check\EnvironmentCheck;
use SymfonyHealthCheckBundle\Check\RedisCheck;
use SymfonyHealthCheckBundle\Check\StatusUpCheck;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->set('symfony_health_check.redis_adapter_wrapper', RedisAdapterWrapper::class)
        ->private();

    $services->set('symfony_health_check.doctrine_check', DoctrineORMCheck::class)
        ->args([service('service_container')])
        ->deprecate(
            'macpaw/symfony-health-check-bundle',
            '1.4.2',
            'The "%service_id%" service alias is deprecated, use symfony_health_check.doctrine_orm_check instead'
        );

    $services->set('symfony_health_check.doctrine_orm_check', DoctrineORMCheck::class)
        ->args([service('service_container')]);

    $services->set('symfony_health_check.doctrine_odm_check', DoctrineODMCheck::class)
        ->args([service('service_container')]);

    $services->set('symfony_health_check.redis_check', RedisCheck::class)
        ->args([service('symfony_health_check.redis_adapter_wrapper')]);

    $services->set('symfony_health_check.environment_check', EnvironmentCheck::class)
        ->args([service('service_container')]);

    $services->set('symfony_health_check.status_up_check', StatusUpCheck::class);
};
