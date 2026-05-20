<?php
/**
 * DDZ 斗地主 - 输入验证与过滤
 * 所有外部输入（$_GET/$_POST）必须通过这些函数获取
 * 
 * 修复: P0#1 (SQL注入 - 输入验证层), P0#2 (房间访问检查)
 */

/**
 * 从 GET 或 POST 中获取字符串参数
 * @param string     $key     参数名
 * @param string|null $default 默认值
 * @return string|null
 */
function get(string $key, ?string $default = null): ?string
{
    $value = $_REQUEST[$key] ?? $default;
    if ($value === null) {
        return null;
    }
    return trim((string) $value);
}

/**
 * 获取整型参数
 * @param string $key     参数名
 * @param int|null $default 默认值
 * @return int|null
 */
function getInt(string $key, ?int $default = null): ?int
{
    $value = get($key);
    if ($value === null) {
        return $default;
    }
    if (!ctype_digit($value) && !is_numeric($value)) {
        return $default;
    }
    return (int) $value;
}

/**
 * 获取字符串参数（非空）
 * @param string $key     参数名
 * @param string|null $default 默认值
 * @return string|null
 */
function getString(string $key, ?string $default = null): ?string
{
    $value = get($key);
    if ($value === null || $value === '') {
        return $default;
    }
    // 过滤危险字符（基本的XSS防护）
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * 获取必需的参数，缺失则返回 400 错误
 * @param string $key 参数名
 * @return string
 */
function getRequired(string $key): string
{
    $value = get($key);
    if ($value === null || $value === '') {
        http_response_code(400);
        echo json_encode([
            'code'    => 400,
            'message' => "缺少必需参数: {$key}",
        ]);
        exit;
    }
    return $value;
}

/**
 * 获取必需的整型参数
 * @param string $key 参数名
 * @return int
 */
function getRequiredInt(string $key): int
{
    $value = getInt($key);
    if ($value === null) {
        http_response_code(400);
        echo json_encode([
            'code'    => 400,
            'message' => "缺少必需参数: {$key}",
        ]);
        exit;
    }
    return $value;
}

/**
 * 从 JSON 请求体中获取数据
 * @return array
 */
function getJsonBody(): array
{
    $raw = file_get_contents('php://input');
    if (empty($raw)) {
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}
