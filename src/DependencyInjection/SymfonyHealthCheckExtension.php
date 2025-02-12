<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\DependencyInjection;

use Composer\InstalledVersions;
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

        $container->getDefinition('symfony_health_check.redis_check')
            ->setArgument(1, $config['redis_dsn']);
    }

    private function loadHealthChecks(
        array $config,
        XmlFileLoader $loader,
        ContainerBuilder $container
    ): void {
        $loader->load('health_checks.xml');

        $healthCheckCollection = $container->findDefinition(HealthController::class);

        $usedChecks = array_column(array_merge($config['health_checks'], $config['ping_checks']), 'id');
        if (in_array('symfony_health_check.redis_check', $usedChecks)) {
            if (!InstalledVersions::isInstalled('symfony/cache')) {
                throw new \RuntimeException('To use RedisCheck you need to install symfony/cache package.');
            }

            if (empty($config['redis_dsn'])) {
                throw new \RuntimeException('To use RedisCheck you need to configure redis_dsn parameter.');
            }
        }

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
