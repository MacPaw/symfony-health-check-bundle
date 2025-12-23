<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Unit\Check;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Result;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyHealthCheckBundle\Check\DoctrineORMCheck;

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
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);
        $platform = $this->createMock(AbstractPlatform::class);
        $queryResult = $this->createMock(Result::class);

        $platform
            ->method('getDummySelectSQL')
            ->willReturn('SELECT 1');

        $connection
            ->method('getDatabasePlatform')
            ->willReturn($platform);

        $connection
            ->method('executeQuery')
            ->willReturn($queryResult);

        $entityManager
            ->method('getConnection')
            ->willReturn($connection);

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
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $connection = $this->createMock(Connection::class);

        $entityManager
            ->method('getConnection')
            ->willReturn($connection);

        $connection
            ->method('getDatabasePlatform')
            ->willThrowException(new Exception('failed getDatabasePlatform'));

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
