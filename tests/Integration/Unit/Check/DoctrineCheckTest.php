<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\Unit\Check;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyHealthCheckBundle\Check\DoctrineCheck;
use SymfonyHealthCheckBundle\Check\StatusUpCheck;
use SymfonyHealthCheckBundle\Exception\ServiceNotFoundException;
use SymfonyHealthCheckBundle\Tests\Integration\Mock\ConnectionMock;
use SymfonyHealthCheckBundle\Tests\Integration\Mock\EntityManagerMock;

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

    public function testDoctrineSuccess(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $entityManager = $this->createMock(EntityManagerMock::class);

        $container
            ->method('has')
            ->with('doctrine.orm.entity_manager')
            ->willReturn(true);

        $container
            ->method('get')
            ->with('doctrine.orm.entity_manager')
            ->willReturn($entityManager);

        $doctrine = new DoctrineCheck($container);

        $result = $doctrine->check();

        self::assertIsArray($result);
        self::assertNotEmpty($result);
        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('connection', $result);
        self::assertSame('doctrine', $result['name']);
        self::assertIsBool($result['connection']);
        self::assertTrue($result['connection']);
    }

    public function testDoctrineFailPing(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $entityManager = $this->createMock(EntityManagerMock::class);
        $connectionMock = $this->createMock(ConnectionMock::class);

        $entityManager
            ->method('getConnection')
            ->with()
            ->willReturn($connectionMock);

        $connectionMock
            ->method('getDatabasePlatform')
            ->with()
            ->will(self::throwException(new Exception()));

        $container
            ->method('has')
            ->with('doctrine.orm.entity_manager')
            ->willReturn(true);

        $container
            ->method('get')
            ->with('doctrine.orm.entity_manager')
            ->willReturn($entityManager);

        $doctrine = new DoctrineCheck($container);

        $result = $doctrine->check();

        self::assertIsArray($result);
        self::assertNotEmpty($result);
        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('connection', $result);
        self::assertSame('doctrine', $result['name']);
        self::assertIsBool($result['connection']);
        self::assertFalse($result['connection']);
    }
}
