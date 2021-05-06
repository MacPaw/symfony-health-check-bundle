<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HealthControllerTest extends WebTestCase
{
    public function testSomething(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        $response = $client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(json_encode([]), $response->getContent());
    }
}
