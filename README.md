## Limitter
此库用于PHP限流算法的实现

## 安装
您可以通过composer来安装这个库:

`composer require loadxes/limitter`

## 业务类
- 滑动时间窗口算法业务类: `Loadxes\Limitter\TimeWindowLimitter`
- 令牌桶算法业务类: `Loadxes\Limitter\TokenBucketLimitter`

## 限流方法
无论您使用哪个业务类, 都可以调用 `setLimit` 方法进行限流, 算法不同, 参数可能不同。

## 方法参数
1. 滑动时间窗格算法`setLimit`参数说明
`setLimit(int $limit, int $second, string $key, string $driver, $handler)`
- `limit`: 在seconds秒限制的请求总数, 限制频次 
- `seconds`: 时间窗口的长度
- `key`: 限制事件的颗粒度控制键
- `driver`: 存储介质,当前支持 `redis` 和 `file`两种驱动值
- `handler`: IO客户端: Redis 需要传递Redis客户端, File传递false

2. 令牌桶算法`setLimit`参数说明
`setLimit(int $cap, int $rate, string $key, string $driver, $handler)`
- `cap`: 令牌桶的容量
- `rate`: 刷新令牌桶的频率
- `key`: 限制事件的颗粒度控制键
- `driver`: 存储介质,当前支持 `redis` 和 `file`两种驱动值
- `handler`: IO客户端: Redis 需要传递Redis客户端, File传递false

## 返回值 bool
- `true`: 获得了进入系统的权限
- `false`: 超出流量限制阈值, 延缓或丢弃请求

## 用法示例
1. 使用`redis`为存储介质的滑动时间窗口示例：
```php
use Loadxes\Limitter\TimeWindowLimitter;

// 1. 您需要自行预先连接一个Redis客户端
$redisClient = ...

// 2. 实例化限流器
$limiter = new TimeWindowLimitter();

// 3. 调用
// 在1s的时间窗口内, 整个系统只允许1个请求进入
$passOrNot = $limiter->setLimit(1, 1, 'global', 'redis', $redisClient)

// 在1s的时间窗口内, 对于用户[uid]请求[event]操作, 只允许请求1次;
$passOrNot = $limiter->setLimit(1, 1, '[event]_[uid]', 'redis', $redisClient)
```

2. 使用`file`为存储介质的滑动时间窗口示例：
```php
use Loadxes\Limitter\TimeWindowLimitter;

// 1. 您需要自行预先连接一个Redis客户端
$redisClient = ...

// 2. 实例化限流器
$limiter = new TimeWindowLimitter();

// 3. 调用
// 在1s的时间窗口内, 整个系统只允许1个请求进入
$passOrNot = $limiter->setLimit(1, 1, 'global', 'file', $redisClient)

// 在1s的时间窗口内, 对于用户[uid]请求[event]操作, 只允许请求1次;
$passOrNot = $limiter->setLimit(1, 1, '[event]_[uid]', 'file', $redisClient)
```

3. 使用`redis`驱动的令牌桶示例:
```php
use Loadxes\Limitter\TokenBucketLimitter;

$redisClient = ...
$limiter = new TokenBucketLimitter();

// 以每一秒向令牌桶放入一个token的速率填充容量为1的令牌桶
// 桶中的令牌耗尽, 还没有新的令牌放入, 则延缓或拒绝用户的所有请求
$passOrNot = $limiter->setLimit(1, 1, 'global', 'redis', $redisClient);

// 以每一秒向令牌桶放入一个token的速率填充容量为1的令牌桶
// 桶中的令牌耗尽, 还没有新的令牌放入, 则延缓或拒绝uid用户对event操作的请求
$passOrNot = $limiter->setLimit(1, 1, '[event]_[uid]', 'redis', $redisClient);
```

4. 使用`file`驱动的令牌桶示例:
```php
use Loadxes\Limitter\TokenBucketLimitter;

$redisClient = ...
$limiter = new TokenBucketLimitter();

// 以每一秒向令牌桶放入一个token的速率填充容量为1的令牌桶
// 桶中的令牌耗尽, 还没有新的令牌放入, 则延缓或拒绝用户的所有请求
$passOrNot = $limiter->setLimit(1, 1, 'global', 'file', $redisClient);

// 以每一秒向令牌桶放入一个token的速率填充容量为1的令牌桶
// 桶中的令牌耗尽, 还没有新的令牌放入, 则延缓或拒绝uid用户对event操作的请求
$passOrNot = $limiter->setLimit(1, 1, '[event]_[uid]', 'file', $redisClient);
```

## 注意事项: 
1. 此库目前属于beta版本
2. 此库在您看到此Readme时, 尚未经过Unit Test;
3. 此库经过了部分集成测试, 如laravel框架; 尚有未覆盖的PHP框架未进行测试.






