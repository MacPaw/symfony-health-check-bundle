<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyHealthCheckBundle\Dto\Response;
use Throwable;

class DoctrineODMCheck implements CheckInterface
{
    private const CHECK_RESULT_NAME = 'doctrine_odm_check';

    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public function check(): Response
    {
        /** @var \Doctrine\ODM\MongoDB\DocumentManager|null $documentManager */
        $documentManager = $this->container->get(
            'doctrine_mongodb.odm.document_manager',
            ContainerInterface::NULL_ON_INVALID_REFERENCE,
        );

        if (!$documentManager) {
            return new Response(self::CHECK_RESULT_NAME, false, 'Document Manager Not Found.');
        }

        $client = $documentManager->getClient();
        $databaseName = $documentManager->getConfiguration()->getDefaultDB() ?? 'admin';

        try {
            $client->selectDatabase($databaseName)->command(['ping' => 1]);
        } catch (Throwable $e) {
            return new Response(self::CHECK_RESULT_NAME, false, $e->getMessage());
        }

        return new Response(self::CHECK_RESULT_NAME, true, 'ok');
    }
}
