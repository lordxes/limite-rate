<?php

namespace Loadxes\Limitter;

class RedisStorage implements StorageInterface
{
    private $redis;

    public function __construct(string $driver, $handler)
    {
        $rds = CacheFactory::createCache('redis', $driver, $handler);
        $this->redis = $rds;
    }

    public function getUsedLimit(array $keys): int
    {
        $result = $this->redis->mget($keys);
        return array_sum($result);
    }
}