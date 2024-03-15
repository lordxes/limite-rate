<?php

namespace Loadxes\Limitter;

/**
 * 令牌桶限制器
 */
class TokenBucketLimitter implements LimitterInterface
{

    /**
     * @param int $limit 限制频次
     * @param int $second 限制时间
     * @param string $key 限制颗粒度
     * @return void
     */
    public function setLimit(int $limit, int $second, string $key): void
    {

    }
}