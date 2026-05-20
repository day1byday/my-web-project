<?php
/**
 * DDZ 斗地主 - 速率限制
 * 基于会话的简单速率限制，防止轮询压垮服务器
 * 
 * 修复: P1#16 (过高频率的轮询请求)
 */

require_once __DIR__ . '/response.php';

/**
 * 检查请求速率限制
 * 
 * @param string $endpoint      端点标识
 * @param int    $maxPerSecond  每秒最大请求数
 * @param int    $burst         突发允许量
 */
function checkRateLimit(string $endpoint, int $maxPerSecond = 5, int $burst = 10): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $key = "rate_{$endpoint}_" . session_id();
    $now = microtime(true);

    $window = $_SESSION[$key] ?? ['start' => $now, 'count' => 0];

    // 滑动窗口：超过1秒重置
    if ($now - $window['start'] > 1.0) {
        $window = ['start' => $now, 'count' => 0];
    }

    // 即时拒绝超过突发限制的请求
    if ($window['count'] >= $burst) {
        error(429, '请求过于频繁，请稍后再试', [
            'retry_after' => 1,
        ]);
    }

    // 检查速率
    $elapsed = $now - $window['start'];
    if ($elapsed > 0 && ($window['count'] / $elapsed) > $maxPerSecond) {
        // 速率超限，但允许一些突发
        if ($window['count'] >= $burst * 0.8) {
            error(429, '请求过于频繁，请稍后再试', [
                'retry_after' => 1,
            ]);
        }
    }

    $window['count']++;
    $_SESSION[$key] = $window;
}

/**
 * 为轮询端点专门设计的宽松限制
 * 允许更高频率的轮询请求
 */
function checkPollRateLimit(): void
{
    // 轮询端点：允许每秒最多10次（100ms间隔），但突发限制为15
    checkRateLimit('poll', 10, 15);
}

/**
 * 为操作端点设计的严格限制
 * 防止出牌/叫地主等操作被滥用
 */
function checkActionRateLimit(): void
{
    // 操作端点：每秒最多3次
    checkRateLimit('action', 3, 5);
}
