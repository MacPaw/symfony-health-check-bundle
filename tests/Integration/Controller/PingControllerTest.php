<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use SymfonyHealthCheckBundle\Check\DoctrineCheck;
use SymfonyHealthCheckBundle\Check\EnvironmentCheck;
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

        $response = $pingController->pingAction();

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

        $response = $pingController->pingAction();

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

        $response = $pingController->pingAction();
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
        $pingController = new PingController();
        $pingController->addHealthCheck(new StatusUpCheck());
        $pingController->addHealthCheck(new EnvironmentCheck(new ContainerBuilder()));

        $response = $pingController->pingAction();

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
        $pingController = new PingController();
        $pingController->addHealthCheck(new EnvironmentCheck(static::bootKernel()->getContainer()));
        $response = $pingController->pingAction();

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
