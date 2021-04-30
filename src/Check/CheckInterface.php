<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

interface CheckInterface
{
    /**
     * @return array<string, bool>
     */
    public function check(): array;
}
