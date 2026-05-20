<?php
/**
 * DDZ 斗地主 - 退出房间
 * 将当前玩家的房间位置设为 0，清除 roomid 会话
 * 
 * 修复: P0#1 (SQL注入), P0#2 (认证), P0#5 (事务)
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
    echo "<script>window.location.href='room.php'; </script>";
    exit;
}

// 查询房间
$row = DB::selectOne(
    'SELECT * FROM room WHERE id = ?',
    [$roomId]
);

if (!$row) {
    unset($_SESSION['roomid']);
    echo "<script>window.location.href='room.php'; </script>";
    exit;
}

$players = json_decode($row['player'], true) ?: [];

// 将当前玩家从房间移除
for ($i = 0; $i < 3; $i++) {
    if ((int) $players[$i] === $playerId) {
        $players[$i] = 0;
        break;
    }
}

// 检查房间是否为空（所有玩家都离开了）
$allGone = true;
foreach ($players as $p) {
    if ((int) $p !== 0) {
        $allGone = false;
        break;
    }
}

$playerJson = json_encode($players);

try {
    DB::transaction(function () use ($playerJson, $roomId, $allGone) {
        if ($allGone) {
            // 空房间直接删除
            DB::execute('DELETE FROM room WHERE id = ?', [$roomId]);
        } else {
            DB::execute(
                'UPDATE room SET player = ? WHERE id = ?',
                [$playerJson, $roomId]
            );
        }
    });

    unset($_SESSION['roomid']);
    echo "<script>window.location.href='room.php'; </script>";
} catch (Throwable $e) {
    serverError('退出房间失败');
}
