<?php

namespace Loadxes\Limitter;


use Exception;

/**
 * 令牌桶限制器
 */
class TokenBucketLimitter
{

    /**
     * 令牌桶限流器
     * @param int $cap  桶容量
     * @param int $rate   刷新速率
     * @param string $key 限制颗粒度
     * @param string $driver  驱动
     * @param void $handler  处理器
     * @throws \Exception
     */
    public function setLimit(int $cap, int $rate, string $key, string $driver, $handler): bool
    {
        // 判断驱动是否支持
        if (!in_array($driver, ["redis", "file"])) {
            throw new \InvalidArgumentException("目前仅支持Redis和File作为限流驱动");
        }

        $ret = false;

        try {
            switch ($driver) {
                case "redis":
                    $ret = RedisStorage::setTokenBucketLimit($cap, $rate, $key, $handler);
                    break;
                case "file":
                    $ret = FileStorage::setTokenBucketLimit($cap, $rate, $key, $handler);
                    break;
            }
        } catch (Exception $e) {
            throw new \Exception($e);
        }

        // 超出流量
        return $ret;
    }
}
