<?php
/**
 * DDZ 斗地主 - 登录 API
 * POST JSON: { "mail": "user@example.com", "password": "xxx" }
 * 成功返回: { "code": 200, "redirect": "index.php", "name": "..." }
 * 失败返回: { "code": 401, "message": "..." }
 * 
 * 修复: P2#24 (空文件), P0#1 (SQL注入), P0#4 (明文密码)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';

$config = require __DIR__ . '/../../config.php';
DB::init($config['db']);

// 只接受 POST 请求
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    badRequest('仅支持 POST 请求');
}

// 解析 JSON body
$body = json_decode(file_get_contents('php://input'), true);
if (!$body) {
    badRequest('请求体格式错误，需要 JSON');
}

$mail = trim($body['mail'] ?? '');
$password = $body['password'] ?? '';

if ($mail === '' || $password === '') {
    badRequest('邮箱和密码不能为空');
}

// 查询用户
$row = DB::selectOne(
    'SELECT * FROM player WHERE mail = ?',
    [$mail]
);

if (!$row) {
    unauthorized('账号或密码错误');
}

$authenticated = false;

// 优先使用 password_hash
if (!empty($row['password_hash'])) {
    $authenticated = password_verify($password, $row['password_hash']);
} 
// 回退明文（未迁移用户），并自动迁移
elseif (!empty($row['password']) && $row['password'] === $password) {
    $authenticated = true;
    $hash = password_hash($password, PASSWORD_BCRYPT);
    DB::execute('UPDATE player SET password_hash = ? WHERE id = ?', [$hash, $row['id']]);
}

if (!$authenticated) {
    unauthorized('账号或密码错误');
}

// 设置登录会话
setLoginSession((int)$row['id'], $row['name'], [
    'duanwei'  => $row['duanwei'] ?? '',
    'chengwei' => $row['chengwei'] ?? '',
    'lihui'    => $row['lihui'] ?? '',
    'bglihui'  => $row['bglihui'] ?? '',
    'touxiang' => $row['touxiang'] ?? '',
    'jinbi'    => $row['jinbi'] ?? 0,
    'hunyu'    => $row['hunyu'] ?? 0,
]);

// 成功响应
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$baseUrl = dirname(dirname($_SERVER['SCRIPT_NAME'])); // /games/ddz

success([
    'redirect' => "{$baseUrl}/index.php",
    'name'     => $row['name'],
    'id'       => (int)$row['id'],
]);
