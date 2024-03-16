<?php

namespace Loadxes\Limitter;

interface LimitterInterface
{
    public function setLimit(int $limit, int $second, string $key, string $driver, $handler);
}