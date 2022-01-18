<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use SymfonyHealthCheckBundle\DependencyInjection\Configuration;

final class ConfigurationTest extends TestCase
{
    public function testProcessConfigurationWithDefaultConfiguration(): void
    {
        $expectedBundleDefaultConfig = [
            'health_checks' => [],
            'ping_checks' => [],
        ];

        self::assertSame($expectedBundleDefaultConfig, $this->processConfiguration([]));
    }

    public function testProcessConfigurationHealthChecks(): void
    {
        $expectedConfig = [
            'health_checks' => [
                ['id' => 'symfony_health_check.doctrine_check'],
            ],
            'ping_checks' => [],
        ];
        $new = ['health_checks' => [['id' => 'symfony_health_check.doctrine_check']], 'ping_checks' => []];

        self::assertSame(
            $expectedConfig,
            $this->processConfiguration($new)
        );
    }

    public function testProcessConfigurationPing(): void
    {
        $expectedConfig = [
            'health_checks' => [],
            'ping_checks' => [
                ['id' => 'symfony_health_check.doctrine_check']
            ],
        ];
        $new = ['health_checks' => [], 'ping_checks' => [['id' => 'symfony_health_check.doctrine_check']]];

        self::assertSame(
            $expectedConfig,
            $this->processConfiguration($new)
        );
    }

    public function testProcessConfigurationPingAndHealthChecks(): void
    {
        $expectedConfig = [
            'health_checks' => [
                ['id' => 'symfony_health_check.doctrine_check']
            ],
            'ping_checks' => [
                ['id' => 'symfony_health_check.doctrine_check']
            ],
        ];
        $new = [
            'health_checks' => [['id' => 'symfony_health_check.doctrine_check']],
            'ping_checks' => [['id' => 'symfony_health_check.doctrine_check']]
        ];

        self::assertSame(
            $expectedConfig,
            $this->processConfiguration($new)
        );
    }

    private function processConfiguration(array $values): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), ['symfony_health_check' => $values]);
    }
}
