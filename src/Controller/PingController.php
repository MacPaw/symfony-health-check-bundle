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
    private ?int $customResponseCode = null;

    public function addHealthCheck(CheckInterface $check): void
    {
        $this->checks[] = $check;
    }

    public function setCustomResponseCode(?int $code): void
    {
        $this->customResponseCode = $code;
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
        $pingCheck = array_map(
            fn($healthCheck) => $healthCheck->check()->toArray(),
            $this->checks
        );

        $code = $this->customResponseCode;

        if (null !== $code) {
            foreach ($pingCheck as $result) {
                if (!$result['result']) {
                    $responseCode = $code;
                    break;
                }
            }
        }

        return new JsonResponse($pingCheck, $responseCode ?? Response::HTTP_OK);
    }
}
