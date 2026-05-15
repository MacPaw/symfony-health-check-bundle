<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Tests\Unit\EventSubscriber;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\RateLimiter\Storage\CacheStorage;
use SymfonyHealthCheckBundle\EventSubscriber\RateLimiterSubscriber;

final class RateLimiterSubscriberTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $events = RateLimiterSubscriber::getSubscribedEvents();

        self::assertArrayHasKey(KernelEvents::REQUEST, $events);
        self::assertSame(['onKernelRequest', 8], $events[KernelEvents::REQUEST]);
    }

    public function testOnKernelRequestSkipsSubRequests(): void
    {
        $subscriber = new RateLimiterSubscriber();
        $event = $this->createRequestEvent(false);

        $subscriber->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    public function testOnKernelRequestSkipsUnknownRoutes(): void
    {
        $subscriber = new RateLimiterSubscriber();
        $event = $this->createRequestEvent(true, 'unknown_route');

        $subscriber->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    public function testOnKernelRequestSkipsWhenNoLimiterConfigured(): void
    {
        $subscriber = new RateLimiterSubscriber(null, null);
        $event = $this->createRequestEvent(true, 'health');

        $subscriber->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    public function testOnKernelRequestAllowsWhenNotRateLimited(): void
    {
        $limiterFactory = $this->createRateLimiterFactory(100);
        $subscriber = new RateLimiterSubscriber($limiterFactory, null);
        $event = $this->createRequestEvent(true, 'health');

        $subscriber->onKernelRequest($event);

        self::assertNull($event->getResponse());
    }

    public function testOnKernelRequestReturns429WhenRateLimitedOnHealth(): void
    {
        $limiterFactory = $this->createRateLimiterFactory(1);
        $subscriber = new RateLimiterSubscriber($limiterFactory, null);

        $event1 = $this->createRequestEvent(true, 'health');
        $subscriber->onKernelRequest($event1);
        self::assertNull($event1->getResponse());

        $event2 = $this->createRequestEvent(true, 'health');
        $subscriber->onKernelRequest($event2);

        self::assertNotNull($event2->getResponse());
        self::assertSame(429, $event2->getResponse()->getStatusCode());
        self::assertTrue($event2->getResponse()->headers->has('X-RateLimit-Remaining'));
        self::assertTrue($event2->getResponse()->headers->has('X-RateLimit-Retry-After'));
        self::assertTrue($event2->getResponse()->headers->has('X-RateLimit-Limit'));
    }

    public function testOnKernelRequestReturns429WhenRateLimitedOnPing(): void
    {
        $limiterFactory = $this->createRateLimiterFactory(1);
        $subscriber = new RateLimiterSubscriber(null, $limiterFactory);

        $event1 = $this->createRequestEvent(true, 'ping');
        $subscriber->onKernelRequest($event1);
        self::assertNull($event1->getResponse());

        $event2 = $this->createRequestEvent(true, 'ping');
        $subscriber->onKernelRequest($event2);

        self::assertNotNull($event2->getResponse());
        self::assertSame(429, $event2->getResponse()->getStatusCode());
    }

    public function testOnKernelRequestUsesClientIpForRateLimiting(): void
    {
        $limiterFactory = $this->createRateLimiterFactory(1);
        $subscriber = new RateLimiterSubscriber($limiterFactory, null);

        $event1 = $this->createRequestEvent(true, 'health', '192.168.1.1');
        $subscriber->onKernelRequest($event1);
        self::assertNull($event1->getResponse());

        $event2 = $this->createRequestEvent(true, 'health', '192.168.1.2');
        $subscriber->onKernelRequest($event2);
        self::assertNull($event2->getResponse());

        $event3 = $this->createRequestEvent(true, 'health', '192.168.1.1');
        $subscriber->onKernelRequest($event3);
        self::assertNotNull($event3->getResponse());
        self::assertSame(429, $event3->getResponse()->getStatusCode());
    }

    public function testResponseContainsErrorMessage(): void
    {
        $limiterFactory = $this->createRateLimiterFactory(1);
        $subscriber = new RateLimiterSubscriber($limiterFactory, null);

        $event1 = $this->createRequestEvent(true, 'health');
        $subscriber->onKernelRequest($event1);

        $event2 = $this->createRequestEvent(true, 'health');
        $subscriber->onKernelRequest($event2);

        self::assertNotNull($event2->getResponse());
        $content = json_decode($event2->getResponse()->getContent() ?: '', true);
        self::assertIsArray($content);
        self::assertArrayHasKey('error', $content);
        self::assertSame('Too Many Requests', $content['error']);
        self::assertArrayHasKey('retry_after', $content);
    }

    private function createRequestEvent(
        bool $isMainRequest,
        ?string $routeName = null,
        ?string $clientIp = '127.0.0.1'
    ): RequestEvent {
        $request = new Request();
        $request->attributes->set('_route', $routeName);

        if ($clientIp !== null) {
            $request->server->set('REMOTE_ADDR', $clientIp);
        }

        $kernel = $this->createMock(HttpKernelInterface::class);

        return new RequestEvent(
            $kernel,
            $request,
            $isMainRequest ? HttpKernelInterface::MAIN_REQUEST : HttpKernelInterface::SUB_REQUEST
        );
    }

    private function createRateLimiterFactory(int $limit): RateLimiterFactory
    {
        $storage = new CacheStorage(new ArrayAdapter());

        return new RateLimiterFactory(
            [
                'id' => 'test_limiter',
                'policy' => 'fixed_window',
                'limit' => $limit,
                'interval' => '1 hour',
            ],
            $storage
        );
    }
}
