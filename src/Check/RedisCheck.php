<?php

declare(strict_types=1);

namespace SymfonyHealthCheckBundle\Check;

use Composer\InstalledVersions;
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

            switch (true) {
                case method_exists($redisConnection, 'ping'):
                    $result = $redisConnection->ping('hello redis');
                    if ($result !== 'hello redis') {
                        return new Response(self::CHECK_RESULT_NAME, false, 'Redis ping failed.');
                    }

                    break;
                case method_exists($redisConnection, 'info'):
                    $result = $redisConnection->info();
                    if (!is_array($result)) {
                        return new Response(self::CHECK_RESULT_NAME, false, 'Redis info failed.');
                    }

                    break;
                default:
                    throw new \InvalidArgumentException('Unsupported redis connection.');
            }

            return new Response(self::CHECK_RESULT_NAME, true, 'ok');
        } catch (\Throwable $e) {
            return new Response(self::CHECK_RESULT_NAME, false, $e->getMessage());
        }
    }
}
