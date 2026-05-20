<?php
/**
 * DDZ 斗地主 - 房间轮询 (room.php 用)
 * 1秒间隔轮询，检查房间状态变化
 * 
 * 返回: "1"=无变化, "-1"=有变化(更新session), "2"=游戏开始
 * 
 * 修复: P0#1, P0#2
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/response.php';

$config = require __DIR__ . '/config.php';
DB::init($config['db']);

requireLogin();

$roomId = $_SESSION['roomid'] ?? '';
if ($roomId === '') {
    echo '1';
    exit;
}

$room = DB::selectOne('SELECT player, prepare, begin FROM room WHERE id = ?', [$roomId]);
if (!$room) {
    echo '1';
    exit;
}

// 游戏开始
if ($room['begin'] === 'ok') {
    $_SESSION['prepare'] = '[1,0,0]';
    echo '2';
    exit;
}

$storedPlayer   = $_SESSION['player'] ?? '';
$storedPrepare  = $_SESSION['prepare'] ?? '';
$currentPlayer  = $room['player'] ?? '';
$currentPrepare = $room['prepare'] ?? '';

if ($currentPlayer === $storedPlayer && $storedPrepare === $currentPrepare) {
    echo '1';
} else {
    echo '-1';
    $_SESSION['player']  = $currentPlayer;
    $_SESSION['prepare'] = $currentPrepare;
}
