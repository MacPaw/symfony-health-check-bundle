<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

use Composer\InstalledVersions;
use Predis\Connection\Cluster\RedisCluster;
use Relay\Relay;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use SymfonyHealthCheckBundle\Dto\Response;
use SymfonyHealthCheckBundle\Adapter\RedisAdapterWrapper;

class RedisCheck implements CheckInterface
{
    private const CHECK_RESULT_NAME = 'redis_check';

    private RedisAdapterWrapper $redisAdapter;
    private ?string $redisDsn;

    public function __construct(
        RedisAdapterWrapper $redisAdapter,
        ?string $redisDsn,
    ) {
        $this->redisAdapter = $redisAdapter;
        $this->redisDsn = $redisDsn;
    }

    public function check(): Response
    {
        if (!InstalledVersions::isInstalled('symfony/cache')) {
            return new Response(
                self::CHECK_RESULT_NAME,
                false,
                'symfony/cache is required to perform redis check.',
            );
        }

        if ($this->redisDsn === null) {
            return new Response(
                self::CHECK_RESULT_NAME,
                false,
                'redis_dsn parameter should be configured to perform this check.',
            );
        }

        try {
            $redisConnection = $this->redisAdapter->createConnection($this->redisDsn);

            $result = false;
            switch (true) {
                case $redisConnection instanceof \Redis:
                case $redisConnection instanceof \Predis\ClientInterface:
                case $redisConnection instanceof Relay:
                    $result = $this->checkForDefaultRedisClientConfiguration($redisConnection);

                    break;
                case $redisConnection instanceof \RedisArray:
                    $result = $this->checkForRedisArrayClient($redisConnection);

                    break;
                case $redisConnection instanceof \RedisCluster:
                    $result = $this->checkForRedisClusterClient($redisConnection);
            }

            if (!$result) {
                return new Response(self::CHECK_RESULT_NAME, false, 'Redis ping failed.');
            }

            return new Response(self::CHECK_RESULT_NAME, true, 'ok');
        } catch (\Throwable $e) {
            return new Response(self::CHECK_RESULT_NAME, false, $e->getMessage());
        }
    }

    private function checkForDefaultRedisClientConfiguration(\Redis|\Predis\ClientInterface $client): bool
    {
        $response = $client->ping();

        if (is_bool($response)) {
            return $response;
        }

        return $this->isValidPingResponse($response);
    }

    private function checkForRedisArrayClient(\RedisArray $client): bool
    {
        $response = $client->ping();

        if (is_bool($response)) {
            return $response;
        }

        // invalid configuration, RedisClient have different response, than one, provided by RedisArray in fact.
        // @phpstan-ignore-next-line
        foreach ($response as $pingResult) {
            if (is_bool($pingResult) && $pingResult) {
                continue;
            }

            if (!$this->isValidPingResponse($pingResult)) {
                return false;
            }
        }

        return true;
    }

    private function checkForRedisClusterClient(\RedisCluster $client): bool
    {
        throw new \RuntimeException('Redis cluster ping is not supported. Please use RedisArray or Redis client.');
    }

    private function isValidPingResponse(string $response): bool
    {
        return in_array(strtolower($response), ['pong', '+pong'], true);
    }
}
