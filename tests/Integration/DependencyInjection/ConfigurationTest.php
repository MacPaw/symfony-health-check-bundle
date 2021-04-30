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
            'health_checks' => []
        ];

        self::assertSame($expectedBundleDefaultConfig, $this->processConfiguration([]));
    }
    
    public function testProcessConfiguration(): void
    {
        $expectedConfig = [
            'health_checks' => [
                ['id' => 'symfony_health_check_bundle.doctrine_check'],
            ]
        ];
        $new = ['health_checks' => [['id' => 'symfony_health_check_bundle.doctrine_check']]];
        
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
