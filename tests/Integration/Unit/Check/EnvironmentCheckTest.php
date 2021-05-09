<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\Unit\Check;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyHealthCheckBundle\Check\EnvironmentCheck;

class EnvironmentCheckTest extends TestCase
{
    /**
     * @var ContainerInterface|MockObject
     */
    private $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testStatusUpCheckSuccess(): void
    {
        $this->container
            ->method('getParameter')
            ->with('kernel.environment')
            ->willReturn('test');

        $result = (new EnvironmentCheck($this->container))->check();

        self::assertIsArray($result);
        self::assertNotEmpty($result);
        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('environment', $result);
        self::assertSame('test', $result['environment']);
        self::assertSame('environment', $result['name']);
    }
}
