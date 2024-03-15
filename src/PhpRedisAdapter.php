<?php

namespace Loadxes\Limitter;

/**
 * phpredis 库适配
 */
class PhpRedisAdapter implements RedisAdapterInterface
{
    private $phpRedis;

    public function __construct($redis)
    {
        $this->phpRedis = $redis;
    }

    public function mget($key)
    {
        return $this->phpRedis->mGet($key);
    }

    public function set($key, $value)
    {
        return $this->phpRedis->set($key, $value);
    }

    public function incrBy($key, $num)
    {
        return $this->phpRedis->incrby($key, $num);
    }

    public function evalMget($mgetScript, $keys, $limit, $currentKey, $expireSeconds)
    {
        return $this->phpRedis->eval($mgetScript);
    }
}