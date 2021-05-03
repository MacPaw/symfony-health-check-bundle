<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

class StatusUpCheck implements CheckInterface
{
    public function check(): array
    {
        return ['status' => 'up'];
    }
}
