<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;

final class RateLimiterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ?RateLimiterFactory $healthLimiterFactory = null,
        private readonly ?RateLimiterFactory $pingLimiterFactory = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 8],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        $limiterFactory = match ($routeName) {
            'health' => $this->healthLimiterFactory,
            'ping' => $this->pingLimiterFactory,
            default => null,
        };

        if (null === $limiterFactory) {
            return;
        }

        $limiter = $limiterFactory->create($request->getClientIp() ?? 'anonymous');
        $limit = $limiter->consume(1);

        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter()->getTimestamp() - time();
            $event->setResponse(new JsonResponse(
                ['error' => 'Too Many Requests', 'retry_after' => $retryAfter],
                Response::HTTP_TOO_MANY_REQUESTS,
                [
                    'X-RateLimit-Remaining' => (string) $limit->getRemainingTokens(),
                    'X-RateLimit-Retry-After' => (string) $retryAfter,
                    'X-RateLimit-Limit' => (string) $limit->getLimit(),
                ]
            ));
        }
    }
}
