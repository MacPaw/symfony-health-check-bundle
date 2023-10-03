<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

final class PingController extends BaseController
{
    /**
     * @Route(
     *     path="/ping",
     *     name="ping",
     *     methods={"GET"}
     * )
     */
    public function check(): JsonResponse
    {
        return $this->checkAction();
    }
}
