<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

use SymfonyHealthCheckBundle\Dto\Response;

class StatusUpCheck implements CheckInterface
{
    public function check(): Response
    {
        return new Response('status', true, 'up');
    }
}
