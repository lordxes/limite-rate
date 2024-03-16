<?php

namespace Loadxes\Limitter;

use Exception;

class LockTool
{
    /**
     * 获取文件锁
     * @throws Exception
     */
    public function getLock($handler): bool
    {
        // 获取当前时间, 用于超时防止死锁
        $currentTime = time();

        // 无法获取文件句柄
        if ($handler === false) {
            return throw new Exception('error: can not get lock file handler');
        }

        while (!flock($handler, LOCK_EX | LOCK_NB)) {
            // 判断获取锁是否超时, 超时决定获取锁失败
            if (time() - $currentTime > 2) {
                return false;
            }

            // 随机数防止雪崩
            usleep(rand(1000, 1200));
        }

        return true;
    }

    /**
     * 释放文件锁
     * @return void
     */
    public function releaseLock($handler)
    {
        if ($handler) {
            $retryTimes = 10;   // 重试次数
            $retryCnt = 0;
            $released = false;  // toggle 开关

            // TODO 万一十次都失败了呢? 加日志告警? 但是对业务影响较大... Think About It!!!
            while ($retryCnt < $retryTimes && !$released) {
                if (flock($handler, LOCK_UN)) {
                    $released = true;
                } else {
                    $retryTimes++;
                    usleep(10000);        // 每0.01秒尝试释放一次锁
                }
            }
        }
    }

}