<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\Unit\Check;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyHealthCheckBundle\Check\DoctrineCheck;
use SymfonyHealthCheckBundle\Check\StatusUpCheck;
use SymfonyHealthCheckBundle\Exception\ServiceNotFoundException;

class DoctrineCheckTest extends TestCase
{
    public function testStatusUpCheckSuccess(): void
    {
        $result = (new StatusUpCheck())->check();

        self::assertIsArray($result);
        self::assertNotEmpty($result);
        self::assertArrayHasKey('status', $result);
        self::assertSame('up', $result['status']);
    }

    public function testDoctrineHasNotFoundException(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->method('has')
            ->with('doctrine.orm.entity_manager')
            ->willReturn(false);

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('Entity Manager Not Found.');

        $doctrine = new DoctrineCheck($container);

        $doctrine->check();
    }

    public function testDoctrineGetNotFoundException(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->method('has')
            ->with('doctrine.orm.entity_manager')
            ->willReturn(true);

        $container
            ->method('get')
            ->with('doctrine.orm.entity_manager')
            ->willReturn(null);

        $this->expectException(ServiceNotFoundException::class);
        $this->expectExceptionMessage('Entity Manager Not Found.');

        $doctrine = new DoctrineCheck($container);

        $doctrine->check();
    }
}
