<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyHealthCheckBundle\Dto\Response;
use Throwable;

class PredisCheck implements CheckInterface
{
    public const PREDIS_CLIENT_CLASS = 'Predis\ClientInterface';

    private const NAME = 'predis';

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function check(): Response
    {
        if (!$client = $this->getPredisClient()) {
            return new Response(self::NAME, false, 'Predis Client not found');
        }

        $key = 'test-' . time() . random_int(0, 1000);
        try {
            if ($client->get($key) !== null) {
                return new Response(self::NAME, false, 'Unable to check predis status');
            }

            $value = '1';
            $client->set($key, $value);
            if ($client->get($key) === $value) {
                $client->del($key);
                return new Response(self::NAME, true, 'ok');
            } else {
                return new Response(self::NAME, false, 'Predis is not functional');
            }
        } catch (Throwable $throwable) {
            return new Response(self::NAME, false, 'Could not check predis status: ' . $throwable->getMessage());
        }
    }

    private function getPredisClient(): ?object
    {
        foreach (
            [
                self::PREDIS_CLIENT_CLASS,
                'SymfonyBundles\RedisBundle\Redis\ClientInterface',
            ] as $class
        ) {
            if ($this->container->has($class)) {
                return $this->container->get($class);
            }
        }

        return null;
    }
}
