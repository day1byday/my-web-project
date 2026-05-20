<?php
/**
 * DDZ 斗地主 - 查找房间 API
 * GET /api/findroom/?roomid=xxx
 * 
 * 修复: P0#1 (SQL注入), P0#2 (认证)
 */

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../lib/db.php';
require_once __DIR__ . '/../../lib/auth.php';
require_once __DIR__ . '/../../lib/response.php';

$config = require __DIR__ . '/../../config.php';
DB::init($config['db']);

requireLogin();

$roomId = $_GET['roomid'] ?? '';
if ($roomId === '' || !is_numeric($roomId)) {
    badRequest('房间ID无效');
}

$row = DB::selectOne(
    'SELECT * FROM room WHERE id = ?',
    [$roomId]
);

if (!$row) {
    notFound('房间不存在');
}

success([
    'player'         => $row['player'] ?? '[]',
    'begin'          => $row['begin'] ?? 'off',
    'prepare'        => $row['prepare'] ?? '[1,0,0]',
    'discard'        => $row['discard'] ?? '[]',
    'discard_player' => $row['discard_player'] ?? '0',
    'discard_type'   => $row['discard_type'] ?? '-1',
    'discard_weight' => $row['discard_weight'] ?? '-1',
]);
