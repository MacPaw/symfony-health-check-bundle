<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Mock\DocumentManager;

class DocumentManagerMock
{
    public function getConfiguration(): ConfigurationMock
    {
        return new ConfigurationMock();
    }

    public function getClient(): ClientMock
    {
        return new ClientMock();
    }
}
