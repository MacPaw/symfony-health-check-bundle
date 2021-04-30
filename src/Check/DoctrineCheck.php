<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyHealthCheckBundle\Exception\ServiceNotFoundException;
use Throwable;

class DoctrineCheck implements CheckInterface
{
    private const CHECK_RESULT_KEY = 'doctrineConnection';

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @throws ServiceNotFoundException
     */
    public function check(): array
    {
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        if ($entityManager === false) {
            throw new ServiceNotFoundException('Entity Manager Not Found.');
        }

        try {
            $entityManager->getConnection()->ping();
        } catch (Throwable $e) {
            return [self::CHECK_RESULT_KEY => false];
        }

        return [self::CHECK_RESULT_KEY => true];
    }
}
