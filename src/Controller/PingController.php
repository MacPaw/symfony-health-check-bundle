<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use SymfonyHealthCheckBundle\Check\CheckInterface;

final class PingController extends AbstractController
{
    /**
     * @var array<CheckInterface>
     */
    private array $checks = [];

    public function addHealthCheck(CheckInterface $check): void
    {
        $this->checks[] = $check;
    }

    /**
     * @Route(
     *     path="/ping",
     *     name="ping",
     *     methods={"GET"}
     * )
     */
    public function pingAction(): JsonResponse
    {
        $pingCheck = [];
        foreach ($this->checks as $healthCheck) {
            $pingCheck[] = $healthCheck->check()->toArray();
        }

        return new JsonResponse($pingCheck, Response::HTTP_OK);
    }
}
