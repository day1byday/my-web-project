<?php
/**
 * DDZ 斗地主 - 叫地主/抢地主/不叫 API
 * GET /api/up/?id=xxx&id2=xxx&id3=xxx&roomid=xxx&prepare=[1,0,0]
 * 
 * 合并了原来的 index.php 和 index2.php 两个实现
 * 消除了 HTTP 循环调用 (api/state/, api/show/)，改为直接 DB 查询
 * 
 * 修复: P0#1, P0#2, P0#5, P1#7 (消除HTTP循环调用), P2#19 (合并重复API)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/cards.php';
require_once __DIR__ . '/../../lib/response.php';

$config = require __DIR__ . '/../../config.php';
DB::init($config['db']);

requireLogin();

$id     = $_GET['id'] ?? '';
$id2    = $_GET['id2'] ?? '';
$id3    = $_GET['id3'] ?? '';
$roomId = $_GET['roomid'] ?? '';
$prepare = $_GET['prepare'] ?? '';

if ($id === '' || $id2 === '' || $id3 === '' || $roomId === '' || $prepare === '') {
    badRequest('参数不完整');
}

// 解析 prepare 数组和当前玩家位置
$arrPrepare = json_decode($prepare, true);
if (!$arrPrepare || count($arrPrepare) !== 3) {
    badRequest('prepare 格式错误');
}

// 计算当前玩家在数组中的位置 ex 和 next
// state 值: 1=叫地主, 2=抢地主, 3=地主已定(出牌)
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

// 直接查询 player weight，不再通过 HTTP 调用 state API
$row = DB::selectOne('SELECT weight FROM player WHERE id = ?', [$id]);
if (!$row) {
    notFound('玩家不存在');
}
$weight = (int) $row['weight'];

try {
    DB::transaction(function () use ($id, $id2, $id3, $roomId, $weight, $ex, $next, $arrPrepare) {
        if ($weight === 0) {
            // 第一次叫地主
            DB::execute("UPDATE player SET weight = '1' WHERE id = ?", [$id]);
            $arrPrepare[$ex] = 0;
            $arrPrepare[$next] = 2;
            $json = json_encode($arrPrepare);
            DB::execute("UPDATE room SET prepare = ? WHERE id = ?", [$json, $roomId]);

            echo json_encode(['status' => '200', 'weight' => '1']);
            exit;
        }
        // weight === 1: 抢地主，正式设置身份
        // 设置地主/农民身份
        DB::execute("UPDATE player SET identity = '地主' WHERE id = ?", [$id]);
        DB::execute("UPDATE player SET identity = '农民' WHERE id IN (?, ?)", [$id2, $id3]);
        // 重置所有玩家 weight
        DB::execute("UPDATE player SET weight = '0' WHERE id IN (?, ?, ?)", [$id, $id2, $id3]);

        // 底牌加入地主手牌 —— 直接查 DB，不调 HTTP API
        $owner = DB::selectOne('SELECT card, over_card FROM player WHERE id = ?', [$id]);
        $hand = json_decode($owner['card'] ?? '[]', true) ?: [];
        $bottoms = json_decode($owner['over_card'] ?? '[]', true) ?: [];

        $newHand = array_merge($hand, $bottoms);
        sortCardsDesc($newHand);
        $jsonHand = json_encode($newHand);

        DB::execute('UPDATE player SET card = ? WHERE id = ?', [$jsonHand, $id]);

        // 设置出牌阶段 prepare
        $ex === 0 ? ($tmp = '[3,0,0]') : ($ex === 1 ? ($tmp = '[0,3,0]') : ($tmp = '[0,0,3]'));
        DB::execute('UPDATE room SET prepare = ? WHERE id = ?', [$tmp, $roomId]);
    });

    echo json_encode(['status' => '200', 'weight' => '2']);
} catch (Throwable $e) {
    serverError('操作失败');
}
