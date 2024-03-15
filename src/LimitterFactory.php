<?php

namespace Loadxes\Limitter;

class LimitterFactory {
    public static function createLimitter(string $method): LimitterInterface
    {
        $limitter = null;

        switch ($method) {
            case 'time_window':
                $limitter = new TimeWindowLimitter();
                break;
            case 'token_bucket':

                break;
        }

        return $limitter;
    }
}