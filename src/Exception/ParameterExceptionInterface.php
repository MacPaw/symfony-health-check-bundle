<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Exception;

interface ParameterExceptionInterface
{
    /**
     * @return iterable[]
     */
    public function getParameters(): array;
}
