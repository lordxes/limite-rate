<?php

namespace Loadxes\Limitter;

class CacheFactory
{
    public static function createCache(string $cacheType, string $driver, $handler)
    {
        // Redis 驱动验参
        if ($cacheType == "redis" && !in_array($driver, ['phpredis', 'predis', 'php_redis_client'])) {
            return new \Exception("暂不支持此Redis驱动");
        }

        // 实例工厂
        switch ($cacheType) {
            case "file":
                break;
            case "redis":
                switch ($driver) {
                    case "phpredis":
                        return new PhpRedisAdapter($handler);
                    case "predis":
                        return new PRedisAdapter($handler);
                    case "php_redis_client":
                        return new PhpRedisClientAdapter($handler);
                }
                break;
        }
    }
}