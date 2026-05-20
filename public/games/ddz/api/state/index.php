<?php
/**
 * DDZ 斗地主 - 玩家状态 API
 * GET /api/state/?roomid=xxx&id=xxx
 * 
 * 返回当前玩家在房间中的 state 和位置
 * 
 * 修复: P0#1, P0#2, P1#10 (严格比较), P1#15 (魔法值消除)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';

$config = require __DIR__ . '/../../config.php';
DB::init($config['db']);

$playerId = requireLogin();

$roomId = $_GET['roomid'] ?? '';
if ($roomId === '' || !is_numeric($roomId)) {
    badRequest('房间ID无效');
}

$room = DB::selectOne('SELECT player, prepare, noplaynum, discard_player FROM room WHERE id = ?', [$roomId]);
if (!$room) {
    notFound('房间不存在');
}

$players  = json_decode($room['player'], true) ?: [];
$prepares = json_decode($room['prepare'], true) ?: [];
$noplaynum = (int)($room['noplaynum'] ?? 0);
$discardPlayer = (int)($room['discard_player'] ?? 0);

// 找到当前玩家位置
$ex = -1;
for ($i = 0; $i < 3; $i++) {
    if ((int)$players[$i] === $playerId) {
        $ex = $i;
        break;
    }
}

if ($ex === -1) {
    notFound('玩家不在该房间');
}

$state = (int)$prepares[$ex];

// 如果连续两次不出且当前玩家是上轮出牌人 → 强制出牌 (state=3)
$forcedPlay = ($discardPlayer === $playerId && $noplaynum === 2 && $state !== 0);

success([
    'state' => $forcedPlay ? 3 : $state,
    'ex'    => $ex,
]);
