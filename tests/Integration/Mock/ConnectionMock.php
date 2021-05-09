<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Integration\Mock;

class ConnectionMock
{
    public function ping(): bool
    {
        return true;
    }
}
