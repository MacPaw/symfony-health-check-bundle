<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyHealthCheckBundle\Dto\Response;
use Throwable;

class EnvironmentCheck implements CheckInterface
{
    private const CHECK_RESULT_KEY = 'environment';

    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function check(): Response
    {
        try {
            /** @var string $env */
            $env = $this->container->getParameter('kernel.environment');
        } catch (Throwable $e) {
            return new Response(self::CHECK_RESULT_KEY, false, 'Could not determine');
        }

        return new Response(self::CHECK_RESULT_KEY, true, 'ok', [$env]);
    }
}
