<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Adapter;

use Relay\Relay;
use Symfony\Component\Cache\Adapter\RedisAdapter;

/**
 * @codeCoverageIgnore - simple wrapper of static methods for adapter class
 */
class RedisAdapterWrapper
{
    public function createConnection(string $dsn, array $options = []): \Redis|\RedisArray|\RedisCluster|\Predis\ClientInterface|Relay
    {
        return RedisAdapter::createConnection($dsn, $options);
    }
}
