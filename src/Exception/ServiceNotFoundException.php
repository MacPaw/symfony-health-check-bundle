<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

class ServiceNotFoundException extends AbstractException
{
    public function __construct(string $message, array $parameters = [])
    {
        parent::__construct($message, $parameters, Response::HTTP_BAD_REQUEST);
    }
}
