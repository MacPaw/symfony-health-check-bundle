<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyHealthCheckBundle\Dto\Response;
use Throwable;

class DoctrineORMCheck implements CheckInterface
{
    private const CHECK_RESULT_NAME = 'doctrine';

    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function check(): Response
    {
        if ($this->container->has('doctrine.orm.entity_manager') === false) {
            return new Response(self::CHECK_RESULT_NAME, false, 'Entity Manager Not Found.');
        }

        /** @var \Doctrine\ORM\EntityManagerInterface|null $entityManager */
        $entityManager = $this->container->get('doctrine.orm.entity_manager');

        if ($entityManager === null) {
            return new Response(self::CHECK_RESULT_NAME, false, 'Entity Manager Not Found.');
        }

        try {
            $connection = $entityManager->getConnection();
            $connection->executeQuery($connection->getDatabasePlatform()->getDummySelectSQL())->free();
        } catch (Throwable $e) {
            return new Response(self::CHECK_RESULT_NAME, false, $e->getMessage());
        }

        return new Response(self::CHECK_RESULT_NAME, true, 'ok');
    }
}
