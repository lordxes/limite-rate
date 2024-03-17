<?php

namespace Loadxes\Limitter;

interface StorageInterface
{

    /**
     * 设置Time Window限流的核心接口
     * @param int $limit  限制阈值
     * @param int $second  时间窗格长度
     * @param string $key  限制事件相关key
     * @param void $handler  处理器
     * @return mixed
     */
    public static function setTimeWindowLimit(int $limit, int $second, string $key, $handler);

    /**
     * 设置令牌桶的核心接口
     * @param int $cap  令牌桶的容量
     * @param int $rate  令牌桶的速率
     * @param string $key  限制事件的颗粒度
     * @param void $handler 存储器
     * @return mixed
     */
    public function setTokenBucketLimit(int $cap, int $rate, string $key, $handler);
}