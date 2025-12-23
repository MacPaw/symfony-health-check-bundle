<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

use Predis\ClientInterface as PredisClientInterface;
use SymfonyHealthCheckBundle\Adapter\RedisAdapterWrapper;
use SymfonyHealthCheckBundle\Dto\Response;

class RedisCheck implements CheckInterface
{
    private const CHECK_RESULT_NAME = 'redis_check';

    public function __construct(
        private readonly RedisAdapterWrapper $redisAdapter,
        private readonly ?string $redisDsn,
    ) {
    }

    public function check(): Response
    {
        if (empty($this->redisDsn)) {
            return new Response(self::CHECK_RESULT_NAME, false, 'Invalid redis dsn definition.');
        }

        try {
            $redisConnection = $this->redisAdapter->createConnection($this->redisDsn);

            $result = match (true) {
                $redisConnection instanceof \Redis => $this->checkForDefaultRedisClient($redisConnection),
                $redisConnection instanceof PredisClientInterface => $this->checkForPredisClient($redisConnection),
                $redisConnection instanceof \RedisArray => $this->checkForRedisArrayClient($redisConnection),
                default => throw new \RuntimeException(sprintf(
                    'Unsupported Redis client type: %s',
                    $redisConnection::class,
                )),
            };

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

    private function checkForPredisClient(PredisClientInterface $client): bool
    {
        /** @var string|bool $response */
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

        /** @var array<string|bool> $response */
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
