<?php

namespace Loadxes\Limitter;

class FileStorage implements StorageInterface
{

    public function __construct()
    {
        // 创建目录
        $directory = "./limit_log/";
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true); // 在此创建目录
        }
    }

    /**
     * 移动时间窗格限流核心实现
     * @param int $limit 限制次数
     * @param int $second 时间窗格
     * @param string $key /
     * @param void $handler 处理器
     * @return bool
     * @throws \Exception
     */
    public static function setTimeWindowLimit(int $limit, int $second, string $key, $handler): bool
    {
        $filename = './limit_log/' . $key . '.txt';
        $fh = fopen($filename, 'a+');
        if ($fh === false) {
            throw new \Exception('无法打开文件句柄');
        }

        // 独占锁
        $locker = new LockTool();
        try {
            $lock = $locker->getLock($fh);
            if ($lock === false) {
                return false;  // 未获取到文件锁
            }

            // 获取到锁开始进行滑动窗格
            $currentTime = time();
            $windowsKeys = [];
            $usedLimit = 0;
            $linesKeeper = [];  // 需要保留下来的有效行;

            // 2. 获取seconds内的总请求数
            for ($i = $second; $i >= 0; $i--) {
                $t = strtotime("-$i second", $currentTime);
                $windowsKeys[] = $t;
            }

            // 3. 读取控制文件
            while (!feof($fh)) {
                $line = fgets($fh);
                if ($line) {
                    // 解析行数据  [timestamp]  [usedLimit]
                    $limitMsg = explode("  ", $line);
                    if (count($limitMsg) == 2 && in_array($limitMsg[0], $windowsKeys)) {
                        $usedLimit += str_replace("\n", "", $limitMsg[1]);
                        $linesKeeper[] = $limitMsg; // 需要保留下来的行;
                    }
                }
            }

            // 判断是否超限
            if ($usedLimit > $limit) {
                return false;
            }

            // 4. 重写文件
            fseek($fh, 0);
            ftruncate($fh, 0);
            $isAppend = 1; // 是否需要追加当前时间
            foreach ($linesKeeper as $lineArr) {
                if ($lineArr[0] == $currentTime) {
                    $singleLimitNum = str_replace("\n", "", $lineArr[1]);
                    $lineArr[1] = $singleLimitNum + 1;
                    $isAppend = 0;  // 不需要追加行
                }
                fwrite($fh, implode("  ", $lineArr) . "\n");
            }

            // 追加当前时间
            if ($isAppend == 1) {
                fwrite($fh, $currentTime . "  1" . "\n");
            }
        } finally {
            // 是否文件锁
            $locker->releaseLock($fh);
            // 关闭句柄
            fclose($fh);
            // 释放内存
            $fh = null;
        }

        return true;
    }

    /**
     * 令牌桶核心功能代码File驱动实现
     * @param int $cap  令牌桶容量
     * @param int $rate   令牌桶刷新速率
     * @param string $key  颗粒度
     * @param void $handler  处理器
     * @return bool
     * @throws \Exception
     */
    public static function setTokenBucketLimit(int $cap, int $rate, string $key, $handler): bool
    {
        $filename = './limit_log/' . $key . '_bucket' . '.txt';
        $fh = fopen($filename, 'a+');
        if ($fh === false) {
            throw new \Exception('无法打开文件句柄');
        }

        // 独占锁
        $locker = new LockTool();
        try {
            $lock = $locker->getLock($fh);
            if ($lock === false) {
                return false;  // 未获取到文件锁
            }

            // 获取到锁开始令牌桶操作
            $token = 0;
            $lastTime = time();
            $currentTime = time();
            $bucketValue = $cap . '-' . $currentTime;
            $lines = [];

            if (filesize($filename) == 0) {
                // 初始化令牌
                fseek($fh, 0);
                ftruncate($fh, 0);
                fwrite($fh, $bucketValue);

                $token = $cap;
                $lastTime = $currentTime;
            } else {
                // 读取控制文件
                while (!feof($fh)) {
                    $line = fgets($fh);
                    $lines[] = $line;
                }

                $tokenInfo = explode('-', $lines[0]);

                if (count($tokenInfo) !== 2) {
                    throw new \Exception('token log info error');
                }
                $token = $tokenInfo[0];
                $lastTime = $tokenInfo[1];
            }

            // 判断是否有剩余令牌
            $elapsedTime = $currentTime - $lastTime;
            $tokenNeedToAdd = $elapsedTime * $rate;

            $token = min($cap, $token + $tokenNeedToAdd);
            $leftToken = $token - 1;

            // 无法拿到令牌
            if ($leftToken < 0) {
                return false;
            }

            // 拿到令牌先更新log
            fseek($fh, 0);
            ftruncate($fh, 0);
            fwrite($fh, $leftToken . '-' . $currentTime);
        } finally {
            // 是否文件锁
            $locker->releaseLock($fh);
            // 关闭句柄
            fclose($fh);
            // 释放内存
            $fh = null;
        }

        // 返回允许通过
        return true;
    }
}
