<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Throwable;

class VersionCheck implements CheckInterface
{
    private const CHECK_RESULT_KEY = 'version';

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function check(): array
    {
        try {
            $this->container->getParameter('app_version');
        } catch (Throwable $e) {
            return [self::CHECK_RESULT_KEY => false];
        }

        return [self::CHECK_RESULT_KEY => true];
    }
}
