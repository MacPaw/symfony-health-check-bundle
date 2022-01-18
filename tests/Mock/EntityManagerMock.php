<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Mock;

class EntityManagerMock
{
    public function getConnection(): ConnectionMock
    {
        return new ConnectionMock();
    }
}
