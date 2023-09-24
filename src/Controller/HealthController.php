<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class HealthController extends BaseController
{
    /**
     * @Route(
     *     path="/health",
     *     name="health",
     *     methods={"GET"}
     * )
     */
    public function check(): JsonResponse
    {
        return $this->checkAction();
    }
}
