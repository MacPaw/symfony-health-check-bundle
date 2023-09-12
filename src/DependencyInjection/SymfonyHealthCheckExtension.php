<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use SymfonyHealthCheckBundle\Controller\HealthController;
use SymfonyHealthCheckBundle\Controller\PingController;

class SymfonyHealthCheckExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('controller.xml');

        $this->loadHealthChecks($config, $loader, $container);
    }

    private function loadHealthChecks(
        array $config,
        XmlFileLoader $loader,
        ContainerBuilder $container
    ): void {
        $loader->load('health_checks.xml');

        $healthCheckCollection = $container->findDefinition(HealthController::class);

        foreach ($config['health_checks'] as $healthCheckConfig) {
            $healthCheckDefinition = new Reference($healthCheckConfig['id']);
            $healthCheckCollection->addMethodCall('addHealthCheck', [$healthCheckDefinition]);
            $healthCheckCollection->addMethodCall('setCustomResponseCode', [$config['health_error_response_code']]);
        }

        $pingCollection = $container->findDefinition(PingController::class);
        foreach ($config['ping_checks'] as $healthCheckConfig) {
            $healthCheckDefinition = new Reference($healthCheckConfig['id']);
            $pingCollection->addMethodCall('addHealthCheck', [$healthCheckDefinition]);
            $pingCollection->addMethodCall('setCustomResponseCode', [$config['ping_error_response_code']]);
        }
    }
}
