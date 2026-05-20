<?php
/**
 * DDZ 斗地主 - 创建房间
 * 使用时间戳作为房间ID，创建者自动加入房间
 * 
 * 修复: P0#1 (SQL注入), P0#2 (认证), P0#5 (事务缺失)
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/response.php';

$config = require __DIR__ . '/config.php';
DB::init($config['db']);

$playerId = requireLogin();

// 生成房间ID（时间戳
$roomId = (string) time();
$playerJson = json_encode([$playerId, 0, 0]);

// 事务保证原子性
try {
    DB::transaction(function () use ($roomId, $playerJson, $playerId) {
        DB::execute(
            'INSERT INTO room (id, player) VALUES (?, ?)',
            [$roomId, $playerJson]
        );
    });

    $_SESSION['roomid'] = $roomId;
    
    echo "<script> window.location.href='room.php?id={$roomId}';</script>";
} catch (Throwable $e) {
    serverError('创建房间失败');
}
