<?php
/**
 * DDZ 斗地主 - 当前登录玩家个人信息 API
 * GET /api/player/me.php
 * 
 * 返回当前会话中玩家的完整档案数据，用于前端页面渲染
 * 独立于游戏内 findplayer API（后者侧重游戏状态）
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';

$config = require __DIR__ . '/../../config.php';
DB::init($config['db']);

$playerId = requireLogin();

$row = DB::selectOne('SELECT * FROM player WHERE id = ?', [$playerId]);

if (!$row) {
    notFound('玩家不存在');
}

success([
    'id'       => (int) $row['id'],
    'name'     => $row['name'],
    'duanwei'  => $row['duanwei'] ?? '',
    'chengwei' => $row['chengwei'] ?? '',
    'lihui'    => $row['lihui'] ?? '',
    'bglihui'  => $row['bglihui'] ?? '',
    'touxiang' => $row['touxiang'] ?? '',
    'jinbi'    => (int) ($row['jinbi'] ?? 0),
    'hunyu'    => (int) ($row['hunyu'] ?? 0),
]);
