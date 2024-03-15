<?php

namespace Loadxes\Limitter;

/**
 * 时间窗格限制器
 */
class TimeWindowLimitter implements LimitterInterface
{

    /**
     * @param int $limit 限制频次
     * @param int $second 限制时间
     * @param string $key 限制颗粒度
     * @param string $cacheType 缓存类型
     * @param string $driver 缓存驱动
     * @param object $handler 缓存处理器
     * @return bool
     */
    public function setLimit(int $limit, int $second, string $key, string $cacheType, string $driver, $handler): bool
    {
        // TODO 验证参数

        // 1. 获取当前时间戳
        $currentTime = time();
        $windowsKeys = [];
        $currentKey = $currentTime . "_" . $key;

        // 2. 获取seconds内的总请求数
        for ($i = $limit; $i >= 0; $i--) {
            $t = strtotime("-$i second", $currentTime);
            $windowsKeys[] = $t . "_" . $key;
        }

        // 使用Lua脚本保证串行执行(原子性)
        $luaScript = <<<LUA
            local keys = ARGV[1]
            local limitNum = ARGV[2]
            local currentKey = ARGV[3]
            local expireSeconds = ARGV[4]
            
            local value = redis.call('mget', unpack(keys))
            local usedLimit = 0
            for i = 1, #keys do
                usedLimit += value[i]
            end
             
            if usedLimit <= limitNum then
                ret = redis.call('setnx', currentKey, 1)
                if ret == 1 then
                    redis.call('expire', expireSeconds)
                else 
                    redis.call('incrby', currentKey, 1)
                end
                return true
            else
                return false
            end
        LUA;

        // 3. 比较总请求数是否超过了limit, 如果超过了limit, 丢弃请求
        $cache = CacheFactory::createCache($cacheType, $driver, $handler);
        return $cache->evalMget($luaScript, $windowsKeys, $limit, $currentKey, $second);
    }
}