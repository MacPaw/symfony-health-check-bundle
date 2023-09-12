<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyHealthCheckBundle\Check\CheckInterface;

final class HealthController extends AbstractController
{
    /**
     * @var array<CheckInterface>
     */
    private array $healthChecks = [];
    private ?int $customResponseCode = null;

    public function setCustomResponseCode(?int $code): void
    {
        $this->customResponseCode = $code;
    }

    public function addHealthCheck(CheckInterface $healthCheck): void
    {
        $this->healthChecks[] = $healthCheck;
    }

    /**
     * @Route(
     *     path="/health",
     *     name="health",
     *     methods={"GET"}
     * )
     */
    public function healthCheckAction(): JsonResponse
    {
        $resultHealthCheck = array_map(
            fn($healthCheck) => $healthCheck->check()->toArray(),
            $this->healthChecks
        );

        $code = $this->customResponseCode;

        if (null !== $code) {
            foreach ($resultHealthCheck as $result) {
                if (!$result['result']) {
                    $responseCode = $code;
                    break;
                }
            }
        }

        return new JsonResponse($resultHealthCheck, $responseCode ?? Response::HTTP_OK);
    }
}
