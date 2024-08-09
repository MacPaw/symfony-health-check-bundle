<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SymfonyHealthCheckBundle\Check\DoctrineCheck;
use SymfonyHealthCheckBundle\Check\EnvironmentCheck;
use SymfonyHealthCheckBundle\Check\PredisCheck;
use SymfonyHealthCheckBundle\Check\StatusUpCheck;
use SymfonyHealthCheckBundle\Controller\PingController;
use TypeError;

class PingControllerTest extends WebTestCase
{
    public function testSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/ping');

        $response = $client->getResponse();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(json_encode([]), $response->getContent());
    }

    public function testAddCheckStatusUpSuccess(): void
    {
        $pingController = new PingController();
        $pingController->addHealthCheck(new StatusUpCheck());

        $response = $pingController->check();

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
        $pingController = new PingController();
        $pingController->addHealthCheck(new EnvironmentCheck(new ContainerBuilder()));

        $response = $pingController->check();

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
        $pingController = new PingController();
        $pingController->addHealthCheck(new DoctrineCheck(new ContainerBuilder()));

        $response = $pingController->check();
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

    public function testPredisCheckServiceNotFoundException(): void
    {
        $healthController = new PingController();
        $healthController->addHealthCheck(new PredisCheck(new ContainerBuilder()));

        $response = $healthController->check();
        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            json_encode([[
                'name' => 'predis',
                'result' => false,
                'message' => 'Predis Client not found',
                'params' => [],
            ]]),
            $response->getContent(),
        );
    }

    public function testTwoCheckSuccess(): void
    {
        $pingController = new PingController();
        $pingController->addHealthCheck(new StatusUpCheck());
        $pingController->addHealthCheck(new EnvironmentCheck(new ContainerBuilder()));

        $response = $pingController->check();

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

    public function testCustomErrorCodeIfOneOfChecksIsFalse(): void
    {
        $pingController = new PingController();
        $pingController->addHealthCheck(new StatusUpCheck());
        $pingController->addHealthCheck(new EnvironmentCheck(new ContainerBuilder()));
        $pingController->setCustomResponseCode(500);

        $response = $pingController->check();

        self::assertSame(500, $response->getStatusCode());
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

    public function testCustomErrorCodeDoesNotAffectSuccessResponse(): void
    {
        $pingController = new PingController();
        $pingController->addHealthCheck(new StatusUpCheck());
        $pingController->setCustomResponseCode(500);

        $response = $pingController->check();

        self::assertSame(200, $response->getStatusCode());
        self::assertSame(
            json_encode([[
                'name' => 'status', 'result' => true, 'message' => 'up', 'params' => [],
            ]]),
            $response->getContent()
        );
    }

    public function testEnvironmentCheckSuccess(): void
    {
        $pingController = new PingController();
        $pingController->addHealthCheck(new EnvironmentCheck(static::bootKernel()->getContainer()));
        $response = $pingController->check();

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

        $pingController = new PingController();
        $pingController->addHealthCheck(new PingController());
    }
}
