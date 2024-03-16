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
     * @return mixed
     */
    public function setTokenBucketLimit();
}