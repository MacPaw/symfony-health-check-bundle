<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use SymfonyHealthCheckBundle\Check\DoctrineCheck;
use SymfonyHealthCheckBundle\Check\EnvironmentCheck;
use SymfonyHealthCheckBundle\Check\StatusUpCheck;
use SymfonyHealthCheckBundle\Controller\HealthController;
use TypeError;

class HealthControllerTest extends WebTestCase
{
    public function testSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health');

        $response = $client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(json_encode([]), $response->getContent());
    }

    public function testAddCheckStatusUpSuccess(): void
    {
        $healthController = new HealthController();
        $healthController->addHealthCheck(new StatusUpCheck());

        $response = $healthController->healthCheckAction();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(json_encode([['status' => 'up']]), $response->getContent());
    }

    public function testEnvironmentCheckCouldNotDetermine(): void
    {
        $healthController = new HealthController();
        $healthController->addHealthCheck(new EnvironmentCheck(new ContainerBuilder()));

        $response = $healthController->healthCheckAction();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            json_encode([['name' => 'environment', 'environment' => 'Could not determine']]),
            $response->getContent()
        );
    }

    public function testDoctrineCheckServiceNotFoundException(): void
    {
        self::expectException(ServiceNotFoundException::class);

        $healthController = new HealthController();
        $healthController->addHealthCheck(new DoctrineCheck(new ContainerBuilder()));

        $healthController->healthCheckAction();
    }

    public function testTwoCheckSuccess(): void
    {
        $healthController = new HealthController();
        $healthController->addHealthCheck(new StatusUpCheck());
        $healthController->addHealthCheck(new EnvironmentCheck(new ContainerBuilder()));

        $response = $healthController->healthCheckAction();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            json_encode([['status' => 'up'], ['name' => 'environment', 'environment' => 'Could not determine']]),
            $response->getContent()
        );
    }
    
    public function testEnvironmentCheckSuccess(): void
    {
        $healthController = new HealthController();
        $healthController->addHealthCheck(new EnvironmentCheck(static::bootKernel()->getContainer()));
        $response = $healthController->healthCheckAction();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            json_encode([['name' => 'environment', 'environment' => 'testing']]),
            $response->getContent()
        );
    }

    public function testAddCheckFailed(): void
    {
        self::expectException(TypeError::class);

        $healthController = new HealthController();
        $healthController->addHealthCheck(new HealthController());
    }
}
