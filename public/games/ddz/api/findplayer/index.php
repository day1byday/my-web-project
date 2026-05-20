<?php
/**
 * DDZ 斗地主 - 获取玩家信息 API
 * GET /api/findplayer/?id=xxx
 * 
 * 安全修复: 仅向卡牌拥有者返回完整卡牌数据，其他人只返回 card_count
 * 
 * 修复: P0#1 (SQL注入), P0#2 (认证), P0#6 (卡牌信息泄露)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';

$config = require __DIR__ . '/../../config.php';
DB::init($config['db']);

$requestingId = requireLogin();

$playerId = $_GET['id'] ?? '';
if ($playerId === '' || !is_numeric($playerId)) {
    badRequest('玩家ID无效');
}

$row = DB::selectOne(
    'SELECT * FROM player WHERE id = ?',
    [$playerId]
);

if (!$row) {
    notFound('玩家不存在');
}

$isSelf = ((int) $playerId === $requestingId);

success([
    'id'       => (int) $row['id'],
    'name'     => $row['name'],
    'touxiang' => $row['touxiang'] ?? '',
    'identity' => $row['identity'] ?? '0',
    // 安全: 仅向卡牌拥有者返回完整卡牌数据
    'card'      => $isSelf ? ($row['card'] ?? '[]') : null,
    'card_count'=> $isSelf ? count(json_decode($row['card'] ?? '[]', true) ?: []) : count(json_decode($row['card'] ?? '[]', true) ?: []),
    'overcard'  => $row['over_card'] ?? '[]',
]);
