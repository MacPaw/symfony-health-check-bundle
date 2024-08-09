<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use SymfonyHealthCheckBundle\Controller\PingController;
use SymfonyHealthCheckBundle\DependencyInjection\SymfonyHealthCheckExtension;
use Throwable;
use SymfonyHealthCheckBundle\Controller\HealthController;

class SymfonyHealthCheckExtensionTest extends TestCase
{
    public function testWithEmptyConfig(): void
    {
        $container = $this->createContainerFromFixture('empty_bundle_config');

        try {
            $container->getDefinition('health_checks');
        } catch (Throwable $exception) {
            self::assertInstanceOf(ServiceNotFoundException::class, $exception);
            self::assertSame(
                'You have requested a non-existent service "health_checks".',
                $exception->getMessage()
            );
        }
    }

    public function testWithEmptyConfigPing(): void
    {
        $container = $this->createContainerFromFixture('empty_bundle_config');

        try {
            $container->getDefinition('ping_checks');
        } catch (Throwable $exception) {
            self::assertInstanceOf(ServiceNotFoundException::class, $exception);
            self::assertSame(
                'You have requested a non-existent service "ping_checks".',
                $exception->getMessage()
            );
        }
    }

    public function testWithFullConfig(): void
    {
        $container = $this->createContainerFromFixture('filled_bundle_config');

        self::assertSame(
            [
                'service_container',
                HealthController::class,
                PingController::class,
                'symfony_health_check.doctrine_check',
                'symfony_health_check.environment_check',
                'symfony_health_check.status_up_check',
                'symfony_health_check.predis_check',
            ],
            array_keys($container->getDefinitions()),
        );
    }

    private function createContainerFromFixture(string $fixtureFile): ContainerBuilder
    {
        $container = new ContainerBuilder();

        $container->registerExtension(new SymfonyHealthCheckExtension());
        $container->getCompilerPassConfig()->setOptimizationPasses([]);
        $container->getCompilerPassConfig()->setRemovingPasses([]);
        $container->getCompilerPassConfig()->setAfterRemovingPasses([]);

        $this->loadFixture($container, $fixtureFile);

        $container->compile();

        return $container;
    }

    protected function loadFixture(ContainerBuilder $container, string $fixtureFile): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/Fixtures'));
        $loader->load($fixtureFile . '.yaml');
    }
}
