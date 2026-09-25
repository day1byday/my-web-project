<?php
declare(strict_types=1);

namespace app\common\service;

use think\facade\Cache;

/**
 * 基于缓存计数器的固定窗口限流
 */
class RateLimitService
{
    /**
     * 检查是否放行
     * @param string $key    缓存键
     * @param int    $limit  窗口内最大次数
     * @param int    $expire 窗口时长（秒）
     * @return bool true=放行 false=超限
     */
    public function hit(string $key, int $limit, int $expire): bool
    {
        $count = (int) Cache::get($key, 0);
        if ($count >= $limit) {
            return false;
        }
        Cache::set($key, $count + 1, $expire);
        return true;
    }

    /**
     * 获取剩余冷却时间（秒）
     */
    public function remaining(string $key, int $expire): int
    {
        $count = (int) Cache::get($key, 0);
        if ($count === 0) {
            return 0;
        }
        // file 驱动无法直接取 TTL，粗略返回窗口值
        return $expire;
    }
}
