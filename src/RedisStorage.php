<?php

namespace Loadxes\Limitter;

class RedisStorage implements StorageInterface
{
    /**
     * 滑动时间窗格Redis驱动实现
     * @param int $limit 限制事件数
     * @param int $second  时间窗格长度
     * @param string $key  颗粒度key
     * @param void $handler  处理器
     * @return mixed
     */
    public static function setTimeWindowLimit(int $limit, int $second, string $key, $handler)
    {
        // 1. 获取当前时间戳
        $currentTime = time();
        $windowsKeys = [];
        $currentKey = $currentTime . "_" . $key;

        // 2. 获取seconds内的总请求数
        for ($i = $second; $i >= 0; $i--) {
            $t = strtotime("-$i second", $currentTime);
            $windowsKeys[] = $t . "_" . $key;
        }

        // 使用Lua脚本保证串行执行(原子性)
        $luaScript = <<<LUA
            local key = KEYS
            local limitNum = tonumber(ARGV[1])
            local currentKey = ARGV[2]
            local expireSeconds = tonumber(ARGV[3])
            local value = redis.call('MGET', unpack(key))
            local usedLimit = 0

            for k, v in pairs(value) do
                if v then
                    usedLimit = usedLimit + tonumber(v)
                end
            end

            if usedLimit < limitNum then
                local ret = redis.call('SETNX', currentKey, 1)
                if ret == 1 then
                    redis.call('EXPIRE', currentKey, expireSeconds * 1.5)
                else
                    redis.call('INCRBY', currentKey, 1)
                end

                return 2
            else
                return 0
            end
LUA;

        return $handler->eval($luaScript, [...$windowsKeys, $limit, $currentKey, $second], count($windowsKeys));
    }

    public function setTokenBucketLimit()
    {
    }
}