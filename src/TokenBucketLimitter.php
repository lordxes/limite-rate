<?php

namespace Loadxes\Limitter;

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
     * @return bool
     * @throws \Exception
     */
    public function setLimit(int $cap, int $rate, string $key, string $driver, $handler)
    {
        // 判断驱动是否支持
        if (!in_array($driver, ["redis", "file"])) {
            return throw new \InvalidArgumentException("目前仅支持Redis和File作为限流驱动");
        }

        $ret = false;

        switch ($driver) {
            case "redis":
                $ret = RedisStorage::setTokenBucketLimit($cap, $rate, $key, $handler);
                break;
            case "file":
                $ret = FileStorage::setTokenBucketLimit($cap, $rate, $key, $handler);
                break;
        }

        // Lua脚本异常
        if ($ret === false) {
            throw new RedisException("error: Internal Error Occur!");
        }

        if ($ret > 10) {
            throw new \Exception('error: redis operation fail.');
        }

        // 未超出流量允许放行
        if ($ret === 1) {
            return true;
        }

        // 超出流量
        return false;
    }
}