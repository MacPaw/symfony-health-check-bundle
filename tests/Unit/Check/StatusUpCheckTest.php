<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Unit\Check;

use PHPUnit\Framework\TestCase;
use SymfonyHealthCheckBundle\Check\StatusUpCheck;

class StatusUpCheckTest extends TestCase
{
    public function testStatusUpCheckSuccess(): void
    {
        $result = (new StatusUpCheck())->check()->toArray();

        self::assertIsArray($result);
        self::assertNotEmpty($result);
        self::assertArrayHasKey('name', $result);
        self::assertArrayHasKey('result', $result);
        self::assertArrayHasKey('message', $result);
        self::assertArrayHasKey('params', $result);
        self::assertSame('status', $result['name']);
        self::assertTrue($result['result']);
        self::assertSame('up', $result['message']);
        self::assertEmpty($result['params']);
    }
}
