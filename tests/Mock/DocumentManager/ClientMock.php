<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Mock\DocumentManager;

class ClientMock
{
    public function selectDatabase(string $databaseName): DatabaseMock
    {
        return new DatabaseMock();
    }
}
