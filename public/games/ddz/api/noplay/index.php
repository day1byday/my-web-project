<?php
/**
 * DDZ 斗地主 - 不出牌（过）API
 * GET /api/noplay/?roomid=xxx&prepare=[4,0,0]
 * 
 * 修复: P0#1, P0#2
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';

$config = require __DIR__ . '/../../config.php';
DB::init($config['db']);

requireLogin();

$roomId = $_GET['roomid'] ?? '';
$prepare = $_GET['prepare'] ?? '';

if ($roomId === '' || $prepare === '') {
    badRequest('参数不完整');
}

// 循环推进 prepare
$newPrepare = match ($prepare) {
    '[3,0,0]' => '[0,4,0]',
    '[0,3,0]' => '[0,0,4]',
    '[0,0,3]' => '[4,0,0]',
    '[4,0,0]' => '[0,4,0]',
    '[0,4,0]' => '[0,0,4]',
    '[0,0,4]' => '[4,0,0]',
    default    => $prepare
};

// 更新 noplaynum
$room = DB::selectOne('SELECT noplaynum FROM room WHERE id = ?', [$roomId]);
$noplaynum = (int)($room['noplaynum'] ?? 0);
$noplaynum = ($noplaynum === 0 || $noplaynum === 1) ? $noplaynum + 1 : 1;

DB::execute(
    'UPDATE room SET prepare = ?, noplaynum = ? WHERE id = ?',
    [$newPrepare, (string)$noplaynum, $roomId]
);

success();
