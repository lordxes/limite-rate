<?php

namespace Loadxes\Limitter;

class FileStorage implements StorageInterface
{

    /**
     * 移动时间窗格限流核心实现
     * @param int $limit  限制次数
     * @param int $second  时间窗格
     * @param string $key  /
     * @param void $handler  处理器
     * @return bool
     * @throws \Exception
     */
    public static function setTimeWindowLimit(int $limit, int $second, string $key, $handler): bool
    {
        // 独占锁
        $locker = new LockTool();
        try {
            $lock = $locker->getLock($handler);
            if ($lock === false){
                return false;  // 未获取到文件锁
            }

            // 获取到锁开始进行滑动窗格
            $currentTime = time();
            $windowsKeys = [];
            $usedLimit = 0;
            $linesKeeper = [];  // 需要保留下来的有效行;

            // 2. 获取seconds内的总请求数
            for ($i = $limit; $i >= 0; $i--) {
                $t = strtotime("-$i second", $currentTime);
                $windowsKeys[] = $t;
            }

            // 3. 读取控制文件
            while (!feof($handler)) {
                $line = fgets($handler);
                if ($line) {
                    // 解析行数据  [timestamp]  [usedLimit]
                    $limitMsg = explode("  ", $line);
                    if (count($limitMsg) == 2 && in_array($limitMsg[0], $windowsKeys)) {
                        $usedLimit += $limitMsg[1];
                        $linesKeeper[] = $limitMsg; // 需要保留下来的行;
                    }
                }
            }

            // 判断是否超限
            if ($usedLimit > $limit) {
                return false;
            }

            // 4. 重写文件
            fseek($handler, 0);
            ftruncate($handler, 0);
            $isAppend = 1; // 是否需要追加当前时间
            foreach ($linesKeeper as $lineArr) {
                if ($lineArr[0] == $currentTime) {
                    $lineArr[1] += 1;
                    $isAppend = 0;  // 不需要追加行
                }
                fwrite($handler, implode("  ", $lineArr));
            }

            // 追加当前时间
            if ($isAppend == 1) {
                fwrite($handler, $currentTime . "  1");
            }
        } finally {
            // 是否文件锁
            $locker->releaseLock($handler);
            // 关闭句柄
            fclose($handler);
            // 释放内存
            $handler = null;
        }

        return true;
    }

    public function setTokenBucketLimit()
    {
    }
}