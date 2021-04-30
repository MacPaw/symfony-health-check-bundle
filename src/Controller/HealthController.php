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
        $resultHealthCheck = [];
        foreach ($this->healthChecks as $healthCheck) {
            $resultHealthCheck[] = $healthCheck->check();
        }

        return new JsonResponse($resultHealthCheck, Response::HTTP_OK);
    }
}
