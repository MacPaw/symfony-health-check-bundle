<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\Unit\Check;

use PHPUnit\Framework\TestCase;
use SymfonyHealthCheckBundle\Check\StatusUpCheck;

class StatusUpCheckTest extends TestCase
{
    public function testStatusUpCheckSuccess(): void
    {
        $result = (new StatusUpCheck())->check();

        self::assertIsArray($result);
        self::assertNotEmpty($result);
        self::assertArrayHasKey('status', $result);
        self::assertSame('up', $result['status']);
    }
}
