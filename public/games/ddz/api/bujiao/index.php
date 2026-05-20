<?php
/**
 * DDZ 斗地主 - 不叫地主 API
 * GET /api/bujiao/?roomid=xxx&prepare=[1,0,0]&id=xxx
 * 
 * 修复: P0#1, P0#2, P1#7 (消除HTTP循环调用)
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
$id = $_GET['id'] ?? '';

if ($roomId === '' || $prepare === '' || $id === '') {
    badRequest('参数不完整');
}

// 解析 prepare 确定当前玩家位置
$arrPrepare = json_decode($prepare, true);
if (!$arrPrepare || count($arrPrepare) !== 3) {
    badRequest('prepare 格式错误');
}

// 找到当前活跃玩家
$ex = -1;
for ($i = 0; $i < 3; $i++) {
    if ((int)$arrPrepare[$i] === 1 || (int)$arrPrepare[$i] === 2) {
        $ex = $i;
        break;
    }
}
if ($ex === -1) {
    badRequest('无法确定当前玩家');
}

$next = ($ex === 0) ? 1 : (($ex === 1) ? 2 : 0);
$up = ($ex === 0) ? 2 : (($ex === 1) ? 0 : 1);

// 判断当前 state
$players = json_decode($_SESSION['player'] ?? '[]', true) ?: [];
$currentPlayer = $players[$ex] ?? 0;

// 查询 noplaynum（从 session 或 DB）
$room = DB::selectOne('SELECT noplaynum FROM room WHERE id = ?', [$roomId]);
$noplaynum = (int)($room['noplaynum'] ?? 0);
$state = (int)$arrPrepare[$ex]; // 1=叫地主, 2=抢地主

if ($state === 2) {
    // 抢地主阶段不叫 → 设 0，下一个变 1
    $arrPrepare[$ex] = 0;
    $arrPrepare[$next] = 1;
    $arrPrepare[$up] = 0;
} else {
    // 叫地主阶段不叫 → 设 0，下一个变 1
    $arrPrepare[$ex] = 0;
    $arrPrepare[$next] = 1;
    $arrPrepare[$up] = 0;
}

$newPrepare = json_encode($arrPrepare);
DB::execute('UPDATE room SET prepare = ? WHERE id = ?', [$newPrepare, $roomId]);

success();
