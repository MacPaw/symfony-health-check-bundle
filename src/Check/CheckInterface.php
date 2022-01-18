<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

use SymfonyHealthCheckBundle\Dto\Response;

interface CheckInterface
{
    public function check(): Response;
}
