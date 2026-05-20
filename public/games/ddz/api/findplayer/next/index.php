<?php
/**
 * DDZ 斗地主 - 获取下一个玩家信息 API
 * GET /api/findplayer/next/?id=xxx&player=[1,2,3]
 * 
 * 给定当前玩家ID和房间玩家数组，返回顺时针下一位玩家信息
 * 安全: 仅向卡牌拥有者返回完整卡牌数据
 * 
 * 修复: P0#1, P0#2, P0#6
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';

$config = require __DIR__ . '/../../config.php';
DB::init($config['db']);

$requestingId = requireLogin();

$currentId = $_GET['id'] ?? '';
$playerJson = $_GET['player'] ?? '';

if ($currentId === '' || !is_numeric($currentId) || $playerJson === '') {
    badRequest('参数无效');
}

$players = json_decode($playerJson, true);
if (!$players || count($players) !== 3) {
    badRequest('玩家列表格式错误');
}

// 找到当前玩家在数组中的位置，取顺时针下一位 (0→1, 1→2, 2→0)
$nextId = null;
for ($i = 0; $i < 3; $i++) {
    if ((int) $players[$i] === (int) $currentId) {
        $nextId = ($i === 2) ? (int) $players[0] : (int) $players[$i + 1];
        break;
    }
}

if ($nextId === null || $nextId === 0) {
    notFound('找不到下一位玩家');
}

$row = DB::selectOne(
    'SELECT * FROM player WHERE id = ?',
    [$nextId]
);

if (!$row) {
    notFound('玩家不存在');
}

$isSelf = ($requestingId === $nextId);

success([
    'id'       => (int) $row['id'],
    'name'     => $row['name'],
    'touxiang' => $row['touxiang'] ?? '',
    'identity' => $row['identity'] ?? '0',
    'card'      => $isSelf ? ($row['card'] ?? '[]') : null,
    'card_count'=> count(json_decode($row['card'] ?? '[]', true) ?: []),
    'overcard'  => $row['over_card'] ?? '[]',
]);
