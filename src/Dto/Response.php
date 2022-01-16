<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Dto;

class Response
{
    private string $name;
    private bool $result;
    private string $message;

    /**
     * @var mixed[]
     */
    private array $params;

    /**
     * @param mixed[] $params
     */
    public function __construct(string $name, bool $result, string $message, array $params = [])
    {
        $this->name = $name;
        $this->result = $result;
        $this->message = $message;
        $this->params = $params;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getResult(): bool
    {
        return $this->result;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return mixed[]
     */
    public function getParams(): array
    {
        return $this->params;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'result' => $this->getResult(),
            'message' => $this->getMessage(),
            'params' => $this->getParams(),
        ];
    }
}
