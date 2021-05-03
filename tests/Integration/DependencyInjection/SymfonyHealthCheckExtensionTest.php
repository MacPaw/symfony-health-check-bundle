<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
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

    public function testNotExistServiceConfig(): void
    {
        try {
            $container = $this->createContainerFromFixture('error_bundle_config');
        } catch (Throwable $exception) {
            self::assertInstanceOf(ServiceNotFoundException::class, $exception);
            self::assertSame(
                'You have requested a non-existent service "not_exists.check".',
                $exception->getMessage()
            );
        }
    }

    public function testWithFullConfig(): void
    {
        $container = $this->createContainerFromFixture('filled_bundle_config');

        self::assertCount(5, $container->getDefinitions());
        self::assertArrayHasKey(HealthController::class, $container->getDefinitions());
        self::assertArrayHasKey('symfony_health_check.doctrine_check', $container->getDefinitions());
        self::assertArrayHasKey('symfony_health_check.environment_check', $container->getDefinitions());
        self::assertArrayHasKey('symfony_health_check.status_up_check', $container->getDefinitions());
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
