<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

interface CheckInterface
{
    /**
     * @return array<string, mixed>
     */
    public function check(): array;
}
