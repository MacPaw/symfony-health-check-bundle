<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Adapter;

use Symfony\Component\Cache\Adapter\RedisAdapter;

/**
 * @codeCoverageIgnore - simple wrapper of static methods for adapter class
 */
class RedisAdapterWrapper
{
    /**
     * @param string $dsn
     * @param array  $options
     *
     * @return \Predis\ClientInterface|\Redis|\RedisArray|\RedisCluster|\Relay\Relay
     */
    public function createConnection(string $dsn, array $options = []): object
    {
        return RedisAdapter::createConnection($dsn, $options);
    }
}
