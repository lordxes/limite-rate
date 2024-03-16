<?php

namespace Loadxes\Limitter;

use RedisException;

/**
 * 时间窗格限制器
 */
class TimeWindowLimitter implements LimitterInterface
{

    /**
     * @param int $limit 限制频次
     * @param int $second 限制时间
     * @param string $key 限制颗粒度
     * @param string $driver
     * @param object $handler 缓存处理器
     * @throws RedisException
     */
    public function setLimit(int $limit, int $second, string $key, string $driver, $handler)
    {
        // 判断驱动是否支持
        if (!in_array($driver, ["redis", "file"])) {
            return throw new \InvalidArgumentException("目前仅支持Redis和File作为限流驱动");
        }

        switch ($driver) {
            case "redis":
                $ret = RedisStorage::setTimeWindowLimit($limit, $second, $key, $handler);

                // Lua脚本异常
                if ($ret === false) {
                    throw new RedisException("error: Lua execute fail, Internal Error Occur!");
                }

                // 未超出流量允许放行
                if ($ret === 1) {
                    return true;
                }

                // 超出流量
                return false;
            case "file":
                $ret = FileStorage::setTimeWindowLimit($limit, $second, $key, $handler);
                return false;
        }
    }
}