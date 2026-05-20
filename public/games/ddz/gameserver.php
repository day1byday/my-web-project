<?php
/**
 * DDZ 斗地主 - 游戏服务器轮询 (game.php 用)
 * 检查游戏结束、状态变化
 * 
 * 返回: "1"=无变化, "-1"=状态变化, "-2"=游戏结束
 * 
 * 重大改进: 不再通过3次HTTP调用 findplayer API，改为直接DB查询
 * 
 * 修复: P1#7 (消除HTTP循环调用), P0#1, P0#2, P1#10
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/response.php';

$config = require __DIR__ . '/config.php';
DB::init($config['db']);

$playerId = requireLogin();

$playerListJson = $_GET['player'] ?? '';
$prepare        = $_GET['prepare'] ?? '';
$roomId         = $_GET['roomid'] ?? '';

if ($playerListJson === '' || $roomId === '') {
    echo '1';
    exit;
}

$players = json_decode($playerListJson, true);
if (!$players || count($players) !== 3) {
    echo '1';
    exit;
}

// 直接查 DB 检查所有玩家手牌（替代3次HTTP调用）
$placeholders = implode(',', array_fill(0, 3, '?'));
$cards = DB::select(
    "SELECT id, card FROM player WHERE id IN ({$placeholders})",
    $players
);

// 检查是否有玩家牌打完了
foreach ($cards as $p) {
    $cardArr = json_decode($p['card'] ?? '[]', true) ?: [];
    if (count($cardArr) === 0) {
        // 游戏结束，重置房间
        DB::execute(
            "UPDATE room SET begin='off', prepare='[1,0,0]' WHERE id = ?",
            [$roomId]
        );
        echo '-2';
        exit;
    }
}

// 检查状态变化
$room = DB::selectOne('SELECT prepare FROM room WHERE id = ?', [$roomId]);
if (!$room) {
    echo '1';
    exit;
}

if ($room['prepare'] === $prepare) {
    echo '1';
} else {
    echo '-1';
    $_SESSION['prepare'] = $room['prepare'];
}
