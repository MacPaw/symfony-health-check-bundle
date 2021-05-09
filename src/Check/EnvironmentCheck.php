<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Throwable;

class EnvironmentCheck implements CheckInterface
{
    private const CHECK_RESULT_KEY = 'environment';

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function check(): array
    {
        $result = ['name' => self::CHECK_RESULT_KEY];

        try {
            $env = $this->container->getParameter('kernel.environment');
        } catch (Throwable $e) {
            return array_merge($result, [self::CHECK_RESULT_KEY => 'Could not determine']);
        }

        return array_merge($result, [self::CHECK_RESULT_KEY => $env]);
    }
}
