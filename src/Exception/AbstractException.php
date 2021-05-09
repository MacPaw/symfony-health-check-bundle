<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Exception;

use Exception;
use Throwable;

abstract class AbstractException extends Exception implements ParameterExceptionInterface
{
    /**
     * @var iterable[]
     */
    private array $parameters;

    /**
     * AbstractException constructor.
     *
     * @param string         $message
     * @param array<mixed>   $parameters
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(
        string $message,
        array $parameters,
        int $code = 0,
        Throwable $previous = null
    ) {
        $this->parameters = $parameters;

        parent::__construct($message, $code, $previous);
    }

    public function getParameters(): array
    {
        return $this->parameters;
    }
}
