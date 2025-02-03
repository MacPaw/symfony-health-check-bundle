<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

use Composer\InstalledVersions;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use SymfonyHealthCheckBundle\Dto\Response;

class RedisCheck implements CheckInterface
{
    private const CHECK_RESULT_NAME = 'redis_check';

    private ?string $redisDsn;

    public function __construct(?string $redisDsn)
    {
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
            $redisConnection = RedisAdapter::createConnection($this->redisDsn);

            if (method_exists($redisConnection, 'ping')) {
                $result = $redisConnection->ping('hello redis');

                if ($result !== 'hello redis') {
                    return new Response(self::CHECK_RESULT_NAME, false, 'Redis ping failed.');
                }
            }

            return new Response(self::CHECK_RESULT_NAME, true, 'ok');
        } catch (\Throwable $e) {
            return new Response(self::CHECK_RESULT_NAME, false, $e->getMessage());
        }
    }
}
