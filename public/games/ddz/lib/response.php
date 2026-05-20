<?php
/**
 * DDZ 斗地主 - 统一 API 响应格式
 * 所有 API 端点使用这些函数返回一致格式的 JSON
 * 
 * 修复: P2#18 (字符串JSON拼接 → json_encode), 统一响应格式
 */

/**
 * 成功响应
 * @param array $data 响应数据
 */
function success(array $data = []): void
{
    $response = array_merge(['code' => 200], $data);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * 错误响应
 * @param int    $httpCode HTTP 状态码
 * @param string $message  错误消息
 * @param array  $extra    额外数据
 */
function error(int $httpCode, string $message, array $extra = []): void
{
    http_response_code($httpCode);
    $response = array_merge([
        'code'    => $httpCode,
        'message' => $message,
    ], $extra);
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── 便捷错误函数 ──

/**
 * 400 - 请求参数错误
 */
function badRequest(string $message = '请求参数错误'): void
{
    error(400, $message);
}

/**
 * 401 - 未授权
 */
function unauthorized(string $message = '未登录'): void
{
    error(401, $message);
}

/**
 * 403 - 禁止访问
 */
function forbidden(string $message = '没有权限'): void
{
    error(403, $message);
}

/**
 * 404 - 资源不存在
 */
function notFound(string $message = '资源不存在'): void
{
    error(404, $message);
}

/**
 * 409 - 冲突（如：房间已满、重复操作等）
 */
function conflict(string $message = '操作冲突'): void
{
    error(409, $message);
}

/**
 * 500 - 服务器内部错误
 */
function serverError(string $message = '服务器内部错误'): void
{
    error(500, $message);
}
