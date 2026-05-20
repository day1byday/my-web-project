<?php
/**
 * DDZ 斗地主 - 准备/取消准备
 * 切换当前玩家的准备状态 (0 ↔ 1)
 * 
 * 修复: P0#1, P0#2, P0#5
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/response.php';

$config = require __DIR__ . '/config.php';
DB::init($config['db']);

$playerId = requireLogin();

$roomId = $_SESSION['roomid'] ?? '';
if ($roomId === '') {
    echo ''; // 不在房间，静默
    exit;
}

$room = DB::selectOne('SELECT player, prepare FROM room WHERE id = ?', [$roomId]);
if (!$room) {
    echo '';
    exit;
}

$players  = json_decode($room['player'], true) ?: [];
$prepares = json_decode($room['prepare'], true) ?: [];

// 找到当前玩家并切换准备状态
for ($i = 0; $i < 3; $i++) {
    if ((int)$players[$i] === $playerId) {
        $prepares[$i] = ((int)$prepares[$i] === 1) ? 0 : 1;
        break;
    }
}

$json = json_encode($prepares);
DB::execute('UPDATE room SET prepare = ? WHERE id = ?', [$json, $roomId]);
$_SESSION['prepare'] = $json;
