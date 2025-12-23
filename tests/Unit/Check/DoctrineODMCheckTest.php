<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Unit\Check;

use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use MongoDB\Client;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyHealthCheckBundle\Check\DoctrineODMCheck;

class DoctrineODMCheckTest extends TestCase
{
    public function testDoctrineODMHasNotFoundException(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->method('get')
            ->with('doctrine_mongodb.odm.document_manager')
            ->willReturn(null);

        $doctrine = new DoctrineODMCheck($container);

        $result = $doctrine->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('doctrine_odm_check', $result['name']);
        self::assertFalse($result['result']);
        self::assertSame('Document Manager Not Found.', $result['message']);
        self::assertIsArray($result['params']);
    }

    public function testDoctrineODMGetNotFoundException(): void
    {
        $container = $this->createMock(ContainerInterface::class);

        $container
            ->method('get')
            ->with('doctrine_mongodb.odm.document_manager')
            ->willReturn(null);

        $doctrine = new DoctrineODMCheck($container);

        $result = $doctrine->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('doctrine_odm_check', $result['name']);
        self::assertFalse($result['result']);
        self::assertSame('Document Manager Not Found.', $result['message']);
        self::assertIsArray($result['params']);
    }

    public function testDoctrineODMSuccess(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $documentManager = $this->createMock(DocumentManager::class);
        $client = $this->createMock(Client::class);
        $database = $this->createMock(Database::class);
        $configuration = $this->createMock(Configuration::class);

        $configuration
            ->method('getDefaultDB')
            ->willReturn('default');

        $documentManager
            ->method('getClient')
            ->willReturn($client);

        $documentManager
            ->method('getConfiguration')
            ->willReturn($configuration);

        $client
            ->method('selectDatabase')
            ->with('default')
            ->willReturn($database);

        $container
            ->method('get')
            ->with('doctrine_mongodb.odm.document_manager')
            ->willReturn($documentManager);

        $doctrine = new DoctrineODMCheck($container);

        $result = $doctrine->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('doctrine_odm_check', $result['name']);
        self::assertTrue($result['result']);
        self::assertSame('ok', $result['message']);
        self::assertIsArray($result['params']);
    }

    public function testDoctrineFailPing(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $documentManager = $this->createMock(DocumentManager::class);
        $client = $this->createMock(Client::class);
        $database = $this->createMock(Database::class);
        $configuration = $this->createMock(Configuration::class);

        $configuration
            ->method('getDefaultDB')
            ->willReturn('default');

        $documentManager
            ->method('getClient')
            ->willReturn($client);

        $documentManager
            ->method('getConfiguration')
            ->willReturn($configuration);

        $client
            ->method('selectDatabase')
            ->with('default')
            ->willReturn($database);

        $database
            ->method('command')
            ->with(['ping' => 1])
            ->willThrowException(new Exception('No suitable servers found'));

        $container
            ->method('get')
            ->with('doctrine_mongodb.odm.document_manager')
            ->willReturn($documentManager);

        $doctrine = new DoctrineODMCheck($container);

        $result = $doctrine->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('doctrine_odm_check', $result['name']);
        self::assertFalse($result['result']);
        self::assertSame('No suitable servers found', $result['message']);
        self::assertIsArray($result['params']);
    }
}
