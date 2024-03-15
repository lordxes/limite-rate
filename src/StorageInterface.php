<?php

namespace Loadxes\Limitter;

interface StorageInterface
{
    /**
     * 获取已经使用过的限制数量
     * @param array $keys
     * @return int
     */
    public function getUsedLimit(array $keys): int;

}