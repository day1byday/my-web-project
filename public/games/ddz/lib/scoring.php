<?php
/**
 * DDZ 斗地主 - 计分系统
 * 实现游戏结束时分数计算和玩家金币更新
 * 
 * 修复: P1#12 (分数/金币从未更新)
 */

require_once __DIR__ . '/db.php';

/**
 * 游戏结束时计算并更新分数
 * 
 * @param int $roomId    房间ID
 * @param int $winnerId  赢家玩家ID（牌先出完的玩家）
 */
function calculateAndUpdateScore(int $roomId, int $winnerId): void
{
    // 获取房间信息
    $room = DB::selectOne("SELECT * FROM room WHERE id = ?", [$roomId]);
    if (!$room) {
        return;
    }

    $players = json_decode($room['player'], true);
    if (!is_array($players) || count($players) !== 3) {
        return;
    }

    // 确定赢家身份
    $winner = DB::selectOne("SELECT id, name, identity, score FROM player WHERE id = ?", [$winnerId]);
    if (!$winner) {
        return;
    }

    $isLandlordWin = ((int) $winner['identity'] === IDENTITY_LANDLORD);

    // 获取底分（默认为1）
    $baseScore = 1; // 可以在 room 表中添加 score 字段来配置

    // 计算得分
    // 地主赢：地主 +2x底分，两个农民各 -1x底分
    // 农民赢：地主 -2x底分，两个农民各 +1x底分
    $multiplier = $isLandlordWin ? 1 : -1;

    DB::transaction(function () use ($players, $winnerId, $baseScore, $multiplier) {
        foreach ($players as $playerId) {
            $player = DB::selectOne(
                "SELECT id, identity, score FROM player WHERE id = ?",
                [(int) $playerId]
            );
            if (!$player) continue;

            $isLandlord = ((int) $player['identity'] === IDENTITY_LANDLORD);
            $scoreChange = 0;

            if ($isLandlord) {
                // 地主：赢+2x底分，输-2x底分
                $scoreChange = $multiplier * $baseScore * 2;
            } elseif ($playerId == $winnerId) {
                // 农民赢家：+1x底分
                $scoreChange = $baseScore;
            } else {
                // 农民输家：-1x底分
                $scoreChange = -$baseScore;
            }

            $newScore = (int) $player['score'] + $scoreChange;
            DB::execute(
                "UPDATE player SET score = ? WHERE id = ?",
                [$newScore, (int) $playerId]
            );
        }
    });
}

/**
 * 获取玩家当前分数和统计
 * @param int $playerId
 * @return array
 */
function getPlayerStats(int $playerId): array
{
    $player = DB::selectOne(
        "SELECT id, name, score, duanwei, chengwei FROM player WHERE id = ?",
        [$playerId]
    );

    if (!$player) {
        return ['code' => 404, 'message' => '玩家不存在'];
    }

    return [
        'code'     => 200,
        'id'       => (int) $player['id'],
        'name'     => $player['name'],
        'score'    => (int) $player['score'],
        'duanwei'  => $player['duanwei'],
        'chengwei' => $player['chengwei'],
    ];
}
