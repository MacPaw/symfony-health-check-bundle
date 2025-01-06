<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Mock\DocumentManager;

class ConfigurationMock
{
    public function getDefaultDB(): ?string
    {
        return 'default';
    }
}
