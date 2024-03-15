<?php

namespace Loadxes\Limitter;

interface RedisAdapterInterface
{
    public function mget(array $key);

    public function set($key, $value);

    public function incrBy($key, $num);

    public function evalMget($mgetScript, $keys, $limit, $currentKey, $expireSeconds);
}