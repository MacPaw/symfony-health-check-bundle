<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SymfonyHealthCheckBundle\Check\DoctrineORMCheck;
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

        $response = $healthController->check();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            json_encode([[
                'name' => 'status', 'result' => true, 'message' => 'up', 'params' => [],
            ]]),
            $response->getContent()
        );
    }

    public function testEnvironmentCheckCouldNotDetermine(): void
    {
        $healthController = new HealthController();
        $healthController->addHealthCheck(new EnvironmentCheck(new ContainerBuilder()));

        $response = $healthController->check();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            json_encode([[
                'name' => 'environment',
                'result' => false,
                'message' => 'Could not determine',
                'params' => []
            ]]),
            $response->getContent()
        );
    }

    public function testDoctrineCheckServiceNotFoundException(): void
    {
        $healthController = new HealthController();
        $healthController->addHealthCheck(new DoctrineORMCheck(new ContainerBuilder()));

        $response = $healthController->check();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            json_encode([[
                'name' => 'doctrine',
                'result' => false,
                'message' => 'Entity Manager Not Found.',
                'params' => []
            ]]),
            $response->getContent()
        );
    }

    public function testTwoCheckSuccess(): void
    {
        $healthController = new HealthController();
        $healthController->addHealthCheck(new StatusUpCheck());
        $healthController->addHealthCheck(new EnvironmentCheck(new ContainerBuilder()));

        $response = $healthController->check();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            json_encode([
                [
                    'name' => 'status',
                    'result' => true,
                    'message' => 'up',
                    'params' => [],
                ],
                [
                    'name' => 'environment',
                    'result' => false,
                    'message' => 'Could not determine',
                    'params' => []
                ]]),
            $response->getContent()
        );
    }

    public function testEnvironmentCheckSuccess(): void
    {
        $healthController = new HealthController();
        $healthController->addHealthCheck(new EnvironmentCheck(static::bootKernel()->getContainer()));
        $response = $healthController->check();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            json_encode([
                [
                    'name' => 'environment',
                    'result' => true,
                    'message' => 'ok',
                    'params' => ['testing']
                ]
            ]),
            $response->getContent()
        );
    }

    public function testAddCheckFailed(): void
    {
        self::expectException(TypeError::class);

        $healthController = new HealthController();
        $healthController->addHealthCheck(new HealthController());
    }

    public function testCustomErrorCodeIfOneOfChecksIsFalse(): void
    {
        $healthController = new HealthController();
        $healthController->addHealthCheck(new EnvironmentCheck(new ContainerBuilder()));
        $healthController->setCustomResponseCode(500);

        $response = $healthController->check();

        self::assertSame(500, $response->getStatusCode());
        self::assertSame(
            json_encode([[
                'name' => 'environment',
                'result' => false,
                'message' => 'Could not determine',
                'params' => []
            ]]),
            $response->getContent()
        );
    }

    public function testCustomErrorCodeDoesNotAffectSuccessResponse(): void
    {
        $healthController = new HealthController();
        $healthController->addHealthCheck(new StatusUpCheck());
        $healthController->setCustomResponseCode(500);

        $response = $healthController->check();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            json_encode([[
                'name' => 'status', 'result' => true, 'message' => 'up', 'params' => [],
            ]]),
            $response->getContent()
        );
    }
}
