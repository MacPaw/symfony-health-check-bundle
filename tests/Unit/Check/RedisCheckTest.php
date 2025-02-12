<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Unit\Check;

use PHPUnit\Framework\TestCase;
use SymfonyHealthCheckBundle\Adapter\RedisAdapterWrapper;
use SymfonyHealthCheckBundle\Check\RedisCheck;

class RedisCheckTest extends TestCase
{
    public function testRedisDsnWasNotProvided(): void
    {
        $adapter = $this->createMock(RedisAdapterWrapper::class);

        $check = new RedisCheck($adapter, null);

        $result = $check->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('redis_check', $result['name']);
        self::assertFalse($result['result']);
        self::assertSame('redis_dsn parameter should be configured to perform this check.', $result['message']);
        self::assertIsArray($result['params']);
    }

    public function testRedisClusterIsNotSupported(): void
    {
        $connectionMock = $this->createMock(\RedisCluster::class);

        $adapter = $this->createMock(RedisAdapterWrapper::class);
        $adapter
            ->method('createConnection')
            ->willReturn($connectionMock);

        $check = new RedisCheck($adapter, 'redis://localhost');

        $result = $check->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('redis_check', $result['name']);
        self::assertFalse($result['result']);
        self::assertSame(
            'Redis cluster ping is not supported. Please use RedisArray or Redis client.',
            $result['message'],
        );
        self::assertIsArray($result['params']);
    }

    public function testItFailsCheckWithExceptionInPing(): void
    {
        $adapter = $this->createMock(RedisAdapterWrapper::class);

        $connectionMock = $this->createMock(\Redis::class);
        $connectionMock
            ->method('ping')
            ->willThrowException(new \Exception('Redis ping failed.'));

        $adapter
            ->method('createConnection')
            ->willReturn($connectionMock);

        $check = new RedisCheck($adapter, 'redis://localhost');

        $result = $check->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('redis_check', $result['name']);
        self::assertFalse($result['result']);
        self::assertSame('Redis ping failed.', $result['message']);
        self::assertIsArray($result['params']);
    }

    public function testItFailsCheckWithInvalidStatusInPing(): void
    {
        $adapter = $this->createMock(RedisAdapterWrapper::class);

        $connectionMock = $this->createMock(\Redis::class);
        $connectionMock
            ->method('ping')
            ->willReturn('something went wrong');

        $adapter
            ->method('createConnection')
            ->willReturn($connectionMock);

        $check = new RedisCheck($adapter, 'redis://localhost');

        $result = $check->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('redis_check', $result['name']);
        self::assertFalse($result['result']);
        self::assertSame('Redis ping failed.', $result['message']);
        self::assertIsArray($result['params']);
    }

    public function testItSuccessCheck(): void
    {
        $connectionMock = $this->createMock(\Redis::class);
        $connectionMock
            ->method('ping')
            ->willReturn('PONG');

        $adapter = $this->createMock(RedisAdapterWrapper::class);
        $adapter
            ->method('createConnection')
            ->willReturn($connectionMock);

        $check = new RedisCheck($adapter, 'redis://localhost');

        $result = $check->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('redis_check', $result['name']);
        self::assertTrue($result['result']);
        self::assertSame('ok', $result['message']);
        self::assertIsArray($result['params']);
    }

    public function testItSuccessCheckWithRedisArrayClient(): void
    {
        $connectionMock = $this->createMock(\RedisArray::class);
        $connectionMock
            ->method('ping')
            ->willReturn(['PONG']);

        $adapter = $this->createMock(RedisAdapterWrapper::class);
        $adapter
            ->method('createConnection')
            ->willReturn($connectionMock);

        $check = new RedisCheck($adapter, 'redis://localhost');

        $result = $check->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('redis_check', $result['name']);
        self::assertTrue($result['result']);
        self::assertSame('ok', $result['message']);
        self::assertIsArray($result['params']);
    }

    public function testItSuccessCheckWithPredisClient(): void
    {
        $connectionMock = $this->getMockBuilder(\Predis\Client::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock->expects($this->once())
            ->method('__call')
            ->with('ping')
            ->willReturn('PONG');

        $adapter = $this->createMock(RedisAdapterWrapper::class);
        $adapter
            ->method('createConnection')
            ->willReturn($connectionMock);

        $check = new RedisCheck($adapter, 'redis://localhost');

        $result = $check->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);

        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);

        self::assertSame('redis_check', $result['name']);
        self::assertTrue($result['result']);
        self::assertSame('ok', $result['message']);
        self::assertIsArray($result['params']);
    }
}
