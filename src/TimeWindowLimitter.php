<?php

namespace Loadxes\Limitter;

use RedisException;

/**
 * 时间窗格限制器
 */
class TimeWindowLimitter
{

    /**
     * 滑动时间窗格限流器
     * @param int $limit 限制频次
     * @param int $second 限制时间
     * @param string $key 限制颗粒度
     * @param string $driver
     * @param object $handler 缓存处理器
     * @return bool
     * @throws RedisException
     * @throws \Exception
     */
    public function setLimit(int $limit, int $second, string $key, string $driver, $handler)
    {
        // 判断驱动是否支持
        if (!in_array($driver, ["redis", "file"])) {
            throw new \InvalidArgumentException("目前仅支持Redis和File作为限流驱动");
        }

        $ret = false;

        switch ($driver) {
            case "redis":
                $ret = RedisStorage::setTimeWindowLimit($limit, $second, $key, $handler);
                break;
            case "file":
                $ret = FileStorage::setTimeWindowLimit($limit, $second, $key, $handler);
                break;
        }

        return $ret;
    }
}
