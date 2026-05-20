<?php
/**
 * DDZ 斗地主 - 认证中间件
 * 所有 API 端点引入此文件以确保用户已登录
 * 
 * 修复: P0#2 (未认证的 API 端点), P1#11 (会话一致性)
 */

// 确保会话已启动
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * 要求用户已登录，否则返回 401
 * @return int 当前登录用户的 ID
 */
function requireLogin(): int
{
    if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
        http_response_code(401);
        echo json_encode([
            'code'    => 401,
            'message' => '请先登录',
        ]);
        exit;
    }
    return (int) $_SESSION['id'];
}

/**
 * 检查用户是否已登录
 * @return bool
 */
function isLoggedIn(): bool
{
    return isset($_SESSION['id']) && !empty($_SESSION['id']);
}

/**
 * 设置登录会话
 * @param int    $playerId 玩家ID
 * @param string $name     玩家名称
 * @param array  $extra    额外会话数据
 */
function setLoginSession(int $playerId, string $name, array $extra = []): void
{
    session_regenerate_id(true); // 防止会话固定攻击
    $_SESSION['id']   = $playerId;
    $_SESSION['name'] = $name;
    foreach ($extra as $key => $value) {
        $_SESSION[$key] = $value;
    }
}

/**
 * 清除登录会话
 */
function clearLoginSession(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}
