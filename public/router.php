<?php
/**
 * PHP 内置服务器路由文件
 * 用法: php -S 0.0.0.0:8001 -t public public/router.php
 *
 * 逻辑：
 * 1. 请求路径匹配 public/ 下的真实文件 → 直接返回（静态资源）
 * 2. 请求路径以 /app/ 开头且非文件 → 返回 Vue SPA 的 index.html
 * 3. 其余请求 → 交给 ThinkPHP index.php 处理
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// 第一步：如果是真实文件，直接返回（CSS/JS/图片等静态资源）
$filePath = __DIR__ . $uri;
if ($uri !== '/' && is_file($filePath)) {
    return false;
}

// 第二步：/app/ 下的路由交给 Vue SPA（前端路由）
// 注意：/app/assets/ 下的真实文件已在第一步处理
if (strpos($uri, '/app/') === 0) {
    $indexFile = __DIR__ . '/app/index.html';
    if (is_file($indexFile)) {
        require $indexFile;
        return true;
    }
}

// 第三步：其余请求走 ThinkPHP
require __DIR__ . '/index.php';
