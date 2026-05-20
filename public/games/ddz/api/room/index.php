<?php
/**
 * DDZ 斗地主 - 加入房间 API
 * GET /api/room/?roomid=xxx
 * 
 * 玩家加入房间逻辑：如果已在房间中则直接进入，否则找空位加入
 * 
 * 修复: P0#1 (SQL注入), P0#2 (认证), P0#5 (事务), P1#8 (双重查询)
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

// 查询房间
$row = DB::selectOne(
    'SELECT * FROM room WHERE id = ?',
    [$roomId]
);

if (!$row) {
    notFound('房间不存在');
}

$players = json_decode($row['player'], true) ?: [];
$_SESSION['player'] = $row['player'];
$_SESSION['prepare'] = $row['prepare'];

// 检查玩家是否已在房间中
$inRoom = false;
for ($i = 0; $i < 3; $i++) {
    if ((int) $players[$i] === $playerId) {
        $inRoom = true;
        $_SESSION['roomid'] = $roomId;
        echo "<script>window.location.href='../../room.php'; </script>";
        exit;
    }
}

// 找空位加入
$joined = false;
for ($i = 0; $i < 3; $i++) {
    if ((int) $players[$i] === 0) {
        $players[$i] = $playerId;
        $joined = true;
        break;
    }
}

if (!$joined) {
    conflict('房间已满');
}

// 事务更新
$playerJson = json_encode($players);
try {
    DB::transaction(function () use ($playerJson, $roomId, $playerId) {
        DB::execute(
            'UPDATE room SET player = ? WHERE id = ?',
            [$playerJson, $roomId]
        );
    });

    $_SESSION['roomid'] = $roomId;
    echo "<script>window.location.href='../../room.php'; </script>";
} catch (Throwable $e) {
    serverError('加入房间失败');
}
