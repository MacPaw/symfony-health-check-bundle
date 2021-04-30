<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

interface CheckInterface
{
    public function check(): array;
}
