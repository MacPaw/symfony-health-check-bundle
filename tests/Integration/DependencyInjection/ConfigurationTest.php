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
            'ping_error_response_code' => null,
            'health_error_response_code' => null,
            'health_checks' => [],
            'ping_checks' => [],
        ];

        self::assertSame($expectedBundleDefaultConfig, $this->processConfiguration([]));
    }

    public function testConfigurationTreeBuilderRootName(): void
    {
        $configuration = new Configuration();
        $treeBuilder = $configuration->getConfigTreeBuilder();
        $rootNodeName = $treeBuilder->buildTree()->getName();

        $this->assertSame('symfony_health_check', $rootNodeName);
    }

    public function testProcessConfigurationHealthChecks(): void
    {
        $expectedConfig = [
            'health_checks' => [
                ['id' => 'symfony_health_check.doctrine_check'],
            ],
            'ping_checks' => [],
            'ping_error_response_code' => null,
            'health_error_response_code' => null,
        ];
        $new = ['health_checks' => [
            ['id' => 'symfony_health_check.doctrine_check']
        ], 'ping_checks' => []];

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
            'ping_error_response_code' => null,
            'health_error_response_code' => null,
        ];
        $new = ['health_checks' => [], 'ping_checks' => [
            ['id' => 'symfony_health_check.doctrine_check']
        ]];

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
            'ping_error_response_code' => null,
            'health_error_response_code' => null,
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

    public function testProcessConfigurationCustomErrorCode(): void
    {
        $expectedConfig = [
            'health_checks' => [
                ['id' => 'symfony_health_check.doctrine_check']
            ],
            'ping_checks' => [
                ['id' => 'symfony_health_check.doctrine_check']
            ],
            'ping_error_response_code' => 404,
            'health_error_response_code' => 500,
        ];
        $new = [
            'health_checks' => [['id' => 'symfony_health_check.doctrine_check']],
            'ping_checks' => [['id' => 'symfony_health_check.doctrine_check']],
            'ping_error_response_code' => 404,
            'health_error_response_code' => 500,
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
