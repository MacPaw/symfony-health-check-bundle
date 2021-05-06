<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use SymfonyHealthCheckBundle\Check\StatusUpCheck;
use SymfonyHealthCheckBundle\Controller\HealthController;
use TypeError;

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

    public function testAddCheck(): void
    {
        $healthController = new HealthController();
        $healthController->addHealthCheck(new StatusUpCheck());

        $response = $healthController->healthCheckAction();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(json_encode([['status' => 'up']]), $response->getContent());
    }

    public function testAddCheckFailed(): void
    {
        self::expectException(TypeError::class);

        $healthController = new HealthController();
        $healthController->addHealthCheck(new HealthController());
    }
}
