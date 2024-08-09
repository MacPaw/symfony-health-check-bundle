<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Unit\Check;

use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use SymfonyHealthCheckBundle\Check\PredisCheck;
use SymfonyHealthCheckBundle\Dto\Response;

class PredisCheckTest extends TestCase
{
    /**
     * @return array{
     *     predisClient:     ?object,
     *     expectedResponse: Response,
     * }[]
     */
    public static function dataProvider(): array
    {
        return [
            'no client' => [
                'predisClient' => null,
                'expectedResponse' => new Response('predis', false, 'Predis Client not found'),
            ],
            'incorrectly initialized client' => [
                'predisClient' => new class {
                    public function get(): string
                    {
                        return 'abracadabra';
                    }
                },
                'expectedResponse' => new Response('predis', false, 'Unable to check predis status'),
            ],
            'exception' => [
                'predisClient' => new class {
                    public function __call(string $name, array $args): string
                    {
                        throw new Exception('test');
                    }
                },
                'expectedResponse' => new Response('predis', false, 'Could not check predis status: test'),
            ],
            'non-working client' => [
                'predisClient' => new class {
                    public function set(): void
                    {
                    }

                    public function get(): ?string
                    {
                        return null;
                    }
                },
                'expectedResponse' => new Response('predis', false, 'Predis is not functional'),
            ],
            'success' => [
                'predisClient' => new class {
                    private array $data = [];

                    public function set(string $key, string $value): void
                    {
                        $this->data[$key] = $value;
                    }

                    public function get(string $key): ?string
                    {
                        return $this->data[$key] ?? null;
                    }

                    public function del(string $key): void
                    {
                        unset($this->data[$key]);
                    }
                },
                'expectedResponse' => new Response('predis', true, 'ok'),
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     */
    public function test(?object $predisClient, Response $expectedResponse): void
    {
        $container = new Container();
        if ($predisClient) {
            $container->set(PredisCheck::PREDIS_CLIENT_CLASS, $predisClient);
        }

        self::assertEquals(
            $expectedResponse,
            (new PredisCheck($container))->check(),
        );
    }
}
