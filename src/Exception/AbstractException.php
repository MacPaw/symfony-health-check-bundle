<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Exception;

use Exception;
use Throwable;

abstract class AbstractException extends Exception implements ParameterExceptionInterface
{
    private array $parameters;
    
    public function __construct(
        string $message,
        array $parameters = [],
        int $code = 0,
        Throwable $previous = null
    )
    {
        $this->parameters = $parameters;
        
        parent::__construct($message, $code, $previous);
    }
    
    public function getParameters(): array
    {
        return $this->parameters;
    }
}
