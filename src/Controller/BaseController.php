<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use SymfonyHealthCheckBundle\Check\CheckInterface;

abstract class BaseController extends AbstractController
{
    /**
     * @var list<CheckInterface>
     */
    private array $checks = [];
    private ?int $customResponseCode = null;

    abstract public function check(): JsonResponse;

    public function addHealthCheck(CheckInterface $check): void
    {
        $this->checks[] = $check;
    }

    public function setCustomResponseCode(?int $code): void
    {
        $this->customResponseCode = $code;
    }

    protected function checkAction(): JsonResponse
    {
        $checkResult = $this->performCheck();
        $responseCode = $this->determineResponseCode($checkResult);

        return new JsonResponse($checkResult, $responseCode);
    }

    /**
     * @return list<array{name: string, result: bool, message: string, params: mixed[]}>
     */
    protected function performCheck(): array
    {
        return array_map(
            static fn(CheckInterface $healthCheck): array => $healthCheck->check()->toArray(),
            $this->checks
        );
    }

    /**
     * @param list<array{name: string, result: bool, message: string, params: mixed[]}> $results
     */
    protected function determineResponseCode(array $results): int
    {
        $code = $this->customResponseCode;
        $responseCode = Response::HTTP_OK;

        if (null !== $code) {
            foreach ($results as $result) {
                if (!$result['result']) {
                    $responseCode = $code;
                    break;
                }
            }
        }

        return $responseCode;
    }
}
