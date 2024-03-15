<?php

namespace Loadxes\Limitter;

/**
 * php_redis_client Redis库适配
 */
class PhpRedisClientAdapter implements RedisAdapterInterface
{
    private $phpRedisClient;

    public function __construct($redis)
    {
        $this->phpRedisClient = $redis;
    }

    public function mget(array $key)
    {
        return $this->phpRedisClient->executeRaw(['MGET', $key]);
    }

    public function set($key, $value)
    {
        return $this->phpRedisClient->executeRaw(['SET', $key, $value]);
    }

    public function incrBy($key, $num)
    {
        return $this->phpRedisClient->executeRaw(['INCRBY', $key, $num]);
    }

    public function evalMget($mgetScript, $keys, $limit, $currentKey, $expireSeconds)
    {
        return $this->phpRedisClient->eval($mgetScript);
    }

}