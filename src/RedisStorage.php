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

    /**
     * 令牌桶Redis实现
     * @param int $cap 令牌桶的容量
     * @param int $rate 令牌桶的速率
     * @param string $key 限制事件的颗粒度
     * @param void $handler 存储器
     * @return mixed
     */
    public static function setTokenBucketLimit(int $cap, int $rate, string $key, $handler)
    {
        // 返回>10表示执行redis操作系统异常
        $luaScript = <<<LUA
            local keys = KEYS
            local bucketKey = KEYS[1]
            local cap = tonumber(ARGV[1])
            local rate = tonumber(ARGV[2])
            local token = 0
            local lastTime = os.time()
            local currentTime = os.time()
            
            // 设置容量-最后获取token的时间戳, 减少IO次数
            local bucketSetRet = redis.call('SETNX', bucketKey, cap .. "-" .. currentTime)
            if bucketSetRet == nil then
                return 11
            end
            
            // 如果key存在, 则重新获取token 和 lastTime
            if bucketSetRet == 0 then
                local bucketRet = redis.call('GET', bucketKey)
                if bucketCapRet == nil then
                    return 14
                end
                
                local temp = {}
                // 将获取结果拆分
                for item in string.gmatch(bucketRet, "[^-]+") do
                    table.insert(temp, item)
                end
                
                token = temp[1]
                lastTime = temp[2]
            end
            
            // 计算桶内剩余令牌数
            local elapsedTime = 0
            local tokenNeedToAdd = 0
            elapsedTime = currentTime - tonumber(lastTime)
            tokenNeedToAdd = elapsedTime * rate
            
            // 剩余的token数
            token = math.min(cap, token + tokenNeedToAdd)
            
            // 没有令牌, 返回0
            local leftToken = token - 1
            if leftToken < 0 then
                return 0
            end
            
            // 还有令牌将剩余令牌更新到bucketKey中
            local updateTokenRet = redis.call('SET', bucketKey, leftToken .. '-' .. currentTime)
            if updateTokenRet == nil then
                return 15
            end
            
            // 通过
            return 2
LUA;
        return $handler->eval($luaScript, [$key . '_bucket', $key . '_bucket_last_time', $cap, $rate], 2);
    }
}