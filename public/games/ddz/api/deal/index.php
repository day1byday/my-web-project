<?php
/**
 * DDZ 斗地主 - 发牌 API
 * GET /api/deal/?id1=1&id2=2&id3=3&roomid=xxx
 * 
 * 事务原子发牌：生成随机牌库 → 三人各17张 + 3张底牌 → 更新DB
 * 
 * 修复: P0#1 (SQL注入), P0#2 (认证), P0#5 (事务 — 4次UPDATE现在是一个原子操作),
 *       P2#14 (卡牌排序问题 — 用 sortCardsDesc 排序)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/cards.php';
require_once __DIR__ . '/../../lib/response.php';

$config = require __DIR__ . '/../../config.php';
DB::init($config['db']);

requireLogin();

$id1 = $_GET['id1'] ?? '';
$id2 = $_GET['id2'] ?? '';
$id3 = $_GET['id3'] ?? '';
$roomId = $_GET['roomid'] ?? '';

if ($id1 === '' || $id2 === '' || $id3 === '' || $roomId === '') {
    badRequest('参数不完整');
}

$_SESSION['player'] = "[{$id1},{$id2},{$id3}]";

// 创建并洗牌
$deck = createShuffledDeck();

// 发牌: 0-16 → 玩家1, 17-33 → 玩家2, 34-50 → 玩家3
$p1Cards = array_slice($deck, 0, 17);
$p2Cards = array_slice($deck, 17, 17);
$p3Cards = array_slice($deck, 34, 17);
$bottomCards = array_slice($deck, 51, 3);

// 排序（权重降序
sortCardsDesc($p1Cards);
sortCardsDesc($p2Cards);
sortCardsDesc($p3Cards);
sortCardsDesc($bottomCards);

$json1 = json_encode($p1Cards);
$json2 = json_encode($p2Cards);
$json3 = json_encode($p3Cards);
$jsonOver = json_encode($bottomCards);

// 事务原子更新
try {
    DB::transaction(function () use ($id1, $id2, $id3, $roomId, $json1, $json2, $json3, $jsonOver) {
        DB::execute('UPDATE player SET card = ? WHERE id = ?', [$json1, $id1]);
        DB::execute('UPDATE player SET card = ? WHERE id = ?', [$json2, $id2]);
        DB::execute('UPDATE player SET card = ? WHERE id = ?', [$json3, $id3]);
        // 重置所有玩家状态
        DB::execute(
            "UPDATE player SET over_card = ?, identity = '', weight = '0' WHERE id IN (?, ?, ?)",
            [$jsonOver, $id1, $id2, $id3]
        );
        // 初始化房间状态
        DB::execute(
            "UPDATE room SET prepare='[1,0,0]', discard='[]', discard_player='', discard_type='', discard_weight='', begin='ok' WHERE id = ?",
            [$roomId]
        );
    });

    success(['status' => '200']);
} catch (Throwable $e) {
    serverError('发牌失败');
}
