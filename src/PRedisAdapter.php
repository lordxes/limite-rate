<?php

namespace Loadxes\Limitter;

/**
 * pRedis 库适配
 */
class PRedisAdapter implements RedisAdapterInterface
{
    private $pRedis;

    public function __construct($redis)
    {
        $this->pRedis = $redis;
    }

    /**
     * 获取key值
     * @param array $key
     * @return mixed
     */
    public function mget(array $key)
    {
        return $this->pRedis->mGet($key);
    }

    /**
     * 设置key
     * @param $key
     * @param $value
     * @return mixed
     */
    public function set($key, $value)
    {
        return $this->pRedis->set($key, $value);
    }

    /**
     * 有效访问+1
     * @param $key
     * @param $num
     * @return mixed
     */
    public function incrBy($key, $num)
    {
        return $this->pRedis->incrby($key, $num);
    }

    public function evalMget($mgetScript, $keys, $limit, $currentKey, $expireSeconds)
    {
        return $this->pRedis->eval($mgetScript);

    }
}