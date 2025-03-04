<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

use SymfonyHealthCheckBundle\Adapter\RedisAdapterWrapper;
use SymfonyHealthCheckBundle\Dto\Response;

class RedisCheck implements CheckInterface
{
    private const CHECK_RESULT_NAME = 'redis_check';

    private RedisAdapterWrapper $redisAdapter;
    private ?string $redisDsn;

    public function __construct(RedisAdapterWrapper $redisAdapter, ?string $redisDsn)
    {
        $this->redisAdapter = $redisAdapter;
        $this->redisDsn = $redisDsn;
    }

    public function check(): Response
    {
        if (empty($this->redisDsn)) {
            return new Response(self::CHECK_RESULT_NAME, false, 'Invalid redis dsn definition.');
        }

        try {
            $redisConnection = $this->redisAdapter->createConnection($this->redisDsn);

            switch (true) {
                case $redisConnection instanceof \Redis:
                    $result = $this->checkForDefaultRedisClient($redisConnection);

                    break;
                case $redisConnection instanceof \Predis\ClientInterface:
                    $result = $this->checkForPredisClient($redisConnection);

                    break;
                case $redisConnection instanceof \RedisArray:
                    $result = $this->checkForRedisArrayClient($redisConnection);

                    break;
                default:
                    throw new \RuntimeException(sprintf(
                        'Unsupported Redis client type: %s',
                        get_class($redisConnection),
                    ));
            }

            if (!$result) {
                return new Response(self::CHECK_RESULT_NAME, false, 'Redis ping failed.');
            }

            return new Response(self::CHECK_RESULT_NAME, true, 'ok');
        } catch (\Throwable $e) {
            return new Response(self::CHECK_RESULT_NAME, false, $e->getMessage());
        }
    }

    private function checkForDefaultRedisClient(\Redis $client): bool
    {
        $response = $client->ping();

        if (is_bool($response)) {
            return $response;
        }

        return $this->isValidPingResponse($response);
    }

    private function checkForPredisClient(\Predis\ClientInterface $client): bool
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
            if (is_bool($pingResult)) {
                continue;
            }

            if (!$this->isValidPingResponse($pingResult)) {
                return false;
            }
        }

        return true;
    }

    private function isValidPingResponse(string $response): bool
    {
        return in_array(strtolower($response), ['pong', '+pong'], true);
    }
}
