<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Unit\Check;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyHealthCheckBundle\Check\DoctrineODMCheck;
use SymfonyHealthCheckBundle\Tests\Mock\DocumentManager\ClientMock;
use SymfonyHealthCheckBundle\Tests\Mock\DocumentManager\ConfigurationMock;
use SymfonyHealthCheckBundle\Tests\Mock\DocumentManager\DatabaseMock;
use SymfonyHealthCheckBundle\Tests\Mock\DocumentManager\DocumentManagerMock;

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
        $documentManager = $this->createMock(DocumentManagerMock::class);

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
        $documentManager = $this->createMock(DocumentManagerMock::class);
        $client = $this->createMock(ClientMock::class);
        $database = $this->createMock(DatabaseMock::class);
        $configuration = $this->createMock(ConfigurationMock::class);

        $configuration
            ->method('getDefaultDB')
            ->willReturn('default');

        $documentManager
            ->method('getClient')
            ->with()
            ->willReturn($client);

        $documentManager
            ->method('getConfiguration')
            ->with()
            ->willReturn($configuration);

        $client
            ->method('selectDatabase')
            ->with('default')
            ->willReturn($database);

        $database
            ->method('command')
            ->with(['ping' => 1])
            ->will(self::throwException(new Exception('No suitable servers found')));

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
