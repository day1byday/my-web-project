<?php
/**
 * DDZ 斗地主 - 获取手牌展示 API
 * GET /api/show/?id=xxx
 * 
 * 返回玩家手牌 ID 列表和对应卡牌名称
 * 
 * 修复: P0#1, P0#2, P2#18 (字符串拼接JSON → json_encode)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/cards.php';
require_once __DIR__ . '/../../lib/response.php';

$config = require __DIR__ . '/../../config.php';
DB::init($config['db']);

$requestingId = requireLogin();

$targetId = $_GET['id'] ?? '';
if ($targetId === '' || !is_numeric($targetId)) {
    badRequest('玩家ID无效');
}

// 只有本人可以看手牌
if ((int)$targetId !== $requestingId) {
    forbidden('只能查看自己的手牌');
}

$player = DB::selectOne('SELECT card, over_card FROM player WHERE id = ?', [$targetId]);
if (!$player) {
    notFound('玩家不存在');
}

$cards    = json_decode($player['card'] ?? '[]', true) ?: [];
$overCards = json_decode($player['over_card'] ?? '[]', true) ?: [];

// 构建卡牌名称数组
$cardNames = [];
foreach ($cards as $cid) {
    $cardNames[] = cardName($cid);
}

success([
    'card'    => implode(',', $cards),
    'over'    => implode(',', $overCards),
    'cardnum' => json_encode($cardNames, JSON_UNESCAPED_UNICODE),
]);
