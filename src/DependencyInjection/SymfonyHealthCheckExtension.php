<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\DependencyInjection;

use Composer\InstalledVersions;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use SymfonyHealthCheckBundle\Controller\HealthController;
use SymfonyHealthCheckBundle\Controller\PingController;
use SymfonyHealthCheckBundle\EventSubscriber\RateLimiterSubscriber;

class SymfonyHealthCheckExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        /** @var array{
         *     health_checks: list<array{id: string}>,
         *     ping_checks: list<array{id: string}>,
         *     redis_dsn: ?string,
         *     health_error_response_code: ?int,
         *     ping_error_response_code: ?int,
         *     rate_limiter: array{
         *         enabled: bool,
         *         health: array{enabled: bool, policy: string, limit: int, interval: string},
         *         ping: array{enabled: bool, policy: string, limit: int, interval: string}
         *     }
         * } $config */
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('controller.php');

        $this->loadHealthChecks($config, $loader, $container);
        $this->loadRateLimiter($config, $container);

        $container->getDefinition('symfony_health_check.redis_check')
            ->setArgument(1, $config['redis_dsn']);
    }

    /**
     * @param array{
     *     health_checks: list<array{id: string}>,
     *     ping_checks: list<array{id: string}>,
     *     redis_dsn: ?string,
     *     health_error_response_code: ?int,
     *     ping_error_response_code: ?int,
     *     rate_limiter: array{
     *         enabled: bool,
     *         health: array{enabled: bool, policy: string, limit: int, interval: string},
     *         ping: array{enabled: bool, policy: string, limit: int, interval: string}
     *     }
     * } $config
     */
    private function loadHealthChecks(
        array $config,
        PhpFileLoader $loader,
        ContainerBuilder $container
    ): void {
        $loader->load('health_checks.php');

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

    /**
     * @param array{
     *     health_checks: list<array{id: string}>,
     *     ping_checks: list<array{id: string}>,
     *     redis_dsn: ?string,
     *     health_error_response_code: ?int,
     *     ping_error_response_code: ?int,
     *     rate_limiter: array{
     *         enabled: bool,
     *         health: array{enabled: bool, policy: string, limit: int, interval: string},
     *         ping: array{enabled: bool, policy: string, limit: int, interval: string}
     *     }
     * } $config
     */
    private function loadRateLimiter(array $config, ContainerBuilder $container): void
    {
        $rateLimiterConfig = $config['rate_limiter'];

        if (!$rateLimiterConfig['enabled']) {
            return;
        }

        if (!InstalledVersions::isInstalled('symfony/rate-limiter')) {
            throw new \RuntimeException(
                'To use rate limiting you need to install symfony/rate-limiter package.'
            );
        }

        $healthFactory = null;
        $pingFactory = null;

        if ($rateLimiterConfig['health']['enabled']) {
            $healthFactory = $this->registerLimiterFactory($container, 'health', $rateLimiterConfig['health']);
        }

        if ($rateLimiterConfig['ping']['enabled']) {
            $pingFactory = $this->registerLimiterFactory($container, 'ping', $rateLimiterConfig['ping']);
        }

        if ($healthFactory !== null || $pingFactory !== null) {
            $container->register(RateLimiterSubscriber::class, RateLimiterSubscriber::class)
                ->setArgument(0, $healthFactory)
                ->setArgument(1, $pingFactory)
                ->addTag('kernel.event_subscriber');
        }
    }

    /**
     * @param array{enabled: bool, policy: string, limit: int, interval: string} $config
     */
    private function registerLimiterFactory(
        ContainerBuilder $container,
        string $name,
        array $config
    ): Reference {
        $serviceId = 'symfony_health_check.rate_limiter.' . $name . '_factory';

        $container->register($serviceId, RateLimiterFactory::class)
            ->setArguments([
                [
                    'id' => $serviceId,
                    'policy' => $config['policy'],
                    'limit' => $config['limit'],
                    'interval' => $config['interval'],
                ],
                new Reference('cache.app'),
                new Reference('lock.factory', ContainerInterface::NULL_ON_INVALID_REFERENCE),
            ]);

        return new Reference($serviceId);
    }
}
