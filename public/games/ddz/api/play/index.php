<?php
/**
 * DDZ 斗地主 - 出牌 API (最关键的安全修复)
 * GET /api/play/?roomid=xxx&id=xxx&prepare=[3,0,0]&num=[0,3,7]&weight=15&type=6
 * 
 * 核心安全改进: 客户端只发送卡牌索引，服务端从 DB 取手牌、提取卡牌、
 * 运行 detectCardType() 服务端验证、比对弃牌堆 — 完全不信任客户端 weight/type
 * 
 * 修复: P0#3 (服务端卡牌验证), P0#1, P0#2, P0#5, P1#7
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/cards.php';
require_once __DIR__ . '/../../lib/card_detector.php';
require_once __DIR__ . '/../../lib/response.php';

$config = require __DIR__ . '/../../config.php';
DB::init($config['db']);

$playerId = requireLogin();

$roomId  = $_GET['roomid'] ?? '';
$prepare = $_GET['prepare'] ?? '';
$numJson = $_GET['num'] ?? '';
// 客户端发来的 weight/type 仅作参考，实际以服务端检测为准
$clientWeight = $_GET['weight'] ?? '';
$clientType   = $_GET['type'] ?? '';

if ($roomId === '' || $prepare === '' || $numJson === '') {
    badRequest('参数不完整');
}

// 解析要出的卡牌索引
$cardIndices = json_decode($numJson, true);
if (!is_array($cardIndices) || count($cardIndices) === 0) {
    badRequest('出牌索引无效');
}

// ── 1. 从 DB 获取当前手牌 ──
$player = DB::selectOne('SELECT card FROM player WHERE id = ?', [$playerId]);
if (!$player) {
    notFound('玩家不存在');
}
$hand = json_decode($player['card'] ?? '[]', true) ?: [];

// ── 2. 根据索引提取卡牌 ──
$cardOut = [];
$remainingHand = $hand;
// 从大到小排序索引以避免 unset 后索引偏移问题
rsort($cardIndices);
foreach ($cardIndices as $idx) {
    if (!isset($remainingHand[$idx])) {
        badRequest('出牌索引越界');
    }
    $cardOut[] = $remainingHand[$idx];
    unset($remainingHand[$idx]);
}
$remainingHand = array_values($remainingHand);

// ── 3. 服务端卡牌类型检测 ──
$result = detectCardType($cardOut);
if (!$result['valid']) {
    badRequest('无效的牌型');
}
$actualType   = $result['type'];
$actualWeight = $result['weight'];

// ── 4. 获取房间弃牌堆信息 ──
$room = DB::selectOne('SELECT * FROM room WHERE id = ?', [$roomId]);
if (!$room) {
    notFound('房间不存在');
}

// ── 5. 比对弃牌堆（如果需要比牌） ──
$discardPlayer = $room['discard_player'] ?? '';
$discardType   = (int)($room['discard_type'] ?? -1);
$discardWeight = (int)($room['discard_weight'] ?? -1);

$canPlay = true;
if ($discardPlayer !== '' && (int)$discardPlayer !== $playerId) {
    // 需要跟弃牌堆比牌
    if ($discardType === $actualType) {
        // 同牌型：比权重
        if ($actualWeight <= $discardWeight) {
            $canPlay = false;
        }
    } else {
        // 不同牌型：只有炸弹(type=4)和王炸(type=0)可以压任意牌
        if ($actualType !== 4 && $actualType !== 0) {
            $canPlay = false;
        }
    }
}

if (!$canPlay) {
    conflict('出牌不够大，无法压过上家');
}

// ── 6. 计算新的 prepare ──
$newPrepare = match ($prepare) {
    '[3,0,0]' => '[0,4,0]',
    '[0,3,0]' => '[0,0,4]',
    '[0,0,3]' => '[4,0,0]',
    '[4,0,0]' => '[0,4,0]',
    '[0,4,0]' => '[0,0,4]',
    '[0,0,4]' => '[4,0,0]',
    default    => $prepare
};

// ── 7. 事务更新 ──
try {
    DB::transaction(function () use (
        $playerId, $roomId, $remainingHand, $cardOut, $newPrepare, $actualType, $actualWeight
    ) {
        // 更新手牌
        DB::execute(
            'UPDATE player SET card = ? WHERE id = ?',
            [json_encode($remainingHand), $playerId]
        );
        // 更新弃牌堆
        DB::execute(
            'UPDATE room SET discard = ?, discard_player = ?, prepare = ?, discard_type = ?, discard_weight = ? WHERE id = ?',
            [json_encode($cardOut), (string)$playerId, $newPrepare, (string)$actualType, (string)$actualWeight, $roomId]
        );
    });

    success();
} catch (Throwable $e) {
    serverError('出牌失败');
}
