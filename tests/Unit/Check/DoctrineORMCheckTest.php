<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Unit\Check;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyHealthCheckBundle\Check\DoctrineORMCheck;
use SymfonyHealthCheckBundle\Tests\Mock\ConnectionMock;
use SymfonyHealthCheckBundle\Tests\Mock\EntityManagerMock;

class DoctrineORMCheckTest extends TestCase
{
    public function testDoctrineORMHasNotFoundException(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->method('has')
            ->with('doctrine.orm.entity_manager')
            ->willReturn(false);

        $doctrine = new DoctrineORMCheck($container);

        $result = $doctrine->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('doctrine', $result['name']);
        self::assertFalse($result['result']);
        self::assertSame('Entity Manager Not Found.', $result['message']);
        self::assertIsArray($result['params']);
    }

    public function testDoctrineORMGetNotFoundException(): void
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

        $doctrine = new DoctrineORMCheck($container);

        $result = $doctrine->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('doctrine', $result['name']);
        self::assertFalse($result['result']);
        self::assertSame('Entity Manager Not Found.', $result['message']);
        self::assertIsArray($result['params']);
    }

    public function testDoctrineORMSuccess(): void
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

        $doctrine = new DoctrineORMCheck($container);

        $result = $doctrine->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('doctrine', $result['name']);
        self::assertTrue($result['result']);
        self::assertSame('ok', $result['message']);
        self::assertIsArray($result['params']);
    }

    public function testDoctrineORMFailPing(): void
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
            ->will(self::throwException(new Exception('failed getDatabasePlatform')));

        $container
            ->method('has')
            ->with('doctrine.orm.entity_manager')
            ->willReturn(true);

        $container
            ->method('get')
            ->with('doctrine.orm.entity_manager')
            ->willReturn($entityManager);

        $doctrine = new DoctrineORMCheck($container);

        $result = $doctrine->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('doctrine', $result['name']);
        self::assertFalse($result['result']);
        self::assertSame('failed getDatabasePlatform', $result['message']);
        self::assertIsArray($result['params']);
    }
}
