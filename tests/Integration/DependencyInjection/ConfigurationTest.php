<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use SymfonyHealthCheckBundle\DependencyInjection\Configuration;

final class ConfigurationTest extends TestCase
{
    private const RATE_LIMITER_DEFAULTS = [
        'enabled' => false,
        'health' => [
            'enabled' => false,
            'policy' => 'fixed_window',
            'limit' => 100,
            'interval' => '60 minutes',
        ],
        'ping' => [
            'enabled' => false,
            'policy' => 'fixed_window',
            'limit' => 100,
            'interval' => '60 minutes',
        ],
    ];

    public function testProcessConfigurationWithDefaultConfiguration(): void
    {
        $expectedBundleDefaultConfig = [
            'ping_error_response_code' => null,
            'health_error_response_code' => null,
            'redis_dsn' => null,
            'health_checks' => [],
            'ping_checks' => [],
            'rate_limiter' => self::RATE_LIMITER_DEFAULTS,
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
            'redis_dsn' => null,
            'rate_limiter' => self::RATE_LIMITER_DEFAULTS,
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
            'redis_dsn' => null,
            'rate_limiter' => self::RATE_LIMITER_DEFAULTS,
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
            'redis_dsn' => null,
            'rate_limiter' => self::RATE_LIMITER_DEFAULTS,
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
            'redis_dsn' => null,
            'rate_limiter' => self::RATE_LIMITER_DEFAULTS,
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

    public function testItProcessConfigurationWithRedisDsn(): void
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
            'redis_dsn' => 'redis://redis',
            'rate_limiter' => self::RATE_LIMITER_DEFAULTS,
        ];
        $new = [
            'health_checks' => [['id' => 'symfony_health_check.doctrine_check']],
            'ping_checks' => [['id' => 'symfony_health_check.doctrine_check']],
            'ping_error_response_code' => 404,
            'health_error_response_code' => 500,
            'redis_dsn' => 'redis://redis',
        ];

        self::assertSame(
            $expectedConfig,
            $this->processConfiguration($new)
        );
    }

    public function testProcessConfigurationRateLimiterEnabled(): void
    {
        $config = $this->processConfiguration([
            'rate_limiter' => [
                'enabled' => true,
                'health' => [
                    'enabled' => true,
                    'policy' => 'sliding_window',
                    'limit' => 50,
                    'interval' => '30 minutes',
                ],
                'ping' => [
                    'enabled' => true,
                    'policy' => 'token_bucket',
                    'limit' => 200,
                    'interval' => '1 hour',
                ],
            ],
        ]);

        self::assertTrue($config['rate_limiter']['enabled']);
        self::assertTrue($config['rate_limiter']['health']['enabled']);
        self::assertSame('sliding_window', $config['rate_limiter']['health']['policy']);
        self::assertSame(50, $config['rate_limiter']['health']['limit']);
        self::assertSame('30 minutes', $config['rate_limiter']['health']['interval']);
        self::assertTrue($config['rate_limiter']['ping']['enabled']);
        self::assertSame('token_bucket', $config['rate_limiter']['ping']['policy']);
        self::assertSame(200, $config['rate_limiter']['ping']['limit']);
        self::assertSame('1 hour', $config['rate_limiter']['ping']['interval']);
    }

    public function testProcessConfigurationRateLimiterPartiallyEnabled(): void
    {
        $config = $this->processConfiguration([
            'rate_limiter' => [
                'enabled' => true,
                'health' => [
                    'enabled' => true,
                ],
            ],
        ]);

        self::assertTrue($config['rate_limiter']['enabled']);
        self::assertTrue($config['rate_limiter']['health']['enabled']);
        self::assertSame('fixed_window', $config['rate_limiter']['health']['policy']);
        self::assertSame(100, $config['rate_limiter']['health']['limit']);
        self::assertFalse($config['rate_limiter']['ping']['enabled']);
    }

    private function processConfiguration(array $values): array
    {
        $processor = new Processor();

        return $processor->processConfiguration(new Configuration(), ['symfony_health_check' => $values]);
    }
}
