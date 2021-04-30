<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Exception;

interface ParameterExceptionInterface
{
    public function getParameters(): array;
}
