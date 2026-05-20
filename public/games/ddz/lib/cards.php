<?php
/**
 * DDZ 斗地主 - 卡牌常量与工具函数
 * 
 * 修复: P1#14 (牌序错误), P1#15 (魔法状态值), P2#17 (卡牌类型常量化)
 */

// ── 卡牌类型常量 ──
define('TYPE_ROCKET',          0);  // 火箭(王炸)
define('TYPE_SINGLE',          1);  // 单张
define('TYPE_PAIR',            2);  // 对子
define('TYPE_TRIPLE',          3);  // 三不带
define('TYPE_BOMB',            4);  // 炸弹(普通)
define('TYPE_TRIPLE_ONE',      5);  // 三带一
define('TYPE_STRAIGHT',        6);  // 顺子(5张起)
define('TYPE_TRIPLE_PAIR',     7);  // 三带一对
define('TYPE_DOUBLE_STRAIGHT', 9);  // 连对
define('TYPE_PLANE',          10);  // 飞机不带
define('TYPE_FOUR_TWO',       11);  // 四带二

// ── 游戏状态常量 ──
define('STATE_IDLE',          0);   // 空闲/等待
define('STATE_CALL_LANDLORD', 1);   // 叫地主阶段
define('STATE_COMPETE',       2);   // 抢地主阶段
define('STATE_FREE_PLAY',     3);   // 自由出牌
define('STATE_MUST_RESPOND',  4);   // 必须应牌
define('STATE_FORCED_PASS',   5);   // 强制过牌

// ── 玩家身份 ──
define('IDENTITY_NONE',       0);   // 未分配
define('IDENTITY_LANDLORD',   1);   // 地主
define('IDENTITY_PEASANT',    2);   // 农民

// ── 卡牌权重表 ──
// 54张牌的权重值，索引即卡牌ID
// 0=小王(17), 1=大王(16), A=15, K=14, ..., 3=4, 2=3
const CARD_WEIGHT = [
    17, 16,                   // 0=小王, 1=大王
    15, 15, 15, 15,           // 2-5: A
    14, 14, 14, 14,           // 6-9: K
    13, 13, 13, 13,           // 10-13: Q
    12, 12, 12, 12,           // 14-17: J
    11, 11, 11, 11,           // 18-21: 10
    10, 10, 10, 10,           // 22-25: 9
     9,  9,  9,  9,           // 26-29: 8
     8,  8,  8,  8,           // 30-33: 7
     7,  7,  7,  7,           // 34-37: 6
     6,  6,  6,  6,           // 38-41: 5
     5,  5,  5,  5,           // 42-45: 4
     4,  4,  4,  4,           // 46-49: 3
     3,  3,  3,  3,           // 50-53: 2
];

/**
 * 获取卡牌的权重值
 * @param int $cardId 卡牌ID (0-53)
 * @return int 权重值，无效ID返回0
 */
function getCardWeight(int $cardId): int
{
    return CARD_WEIGHT[$cardId] ?? 0;
}

/**
 * 按权重降序排列卡牌（大牌在前）
 * 同权重的按花色排列。王炸排在最前面。
 * @param array<int> $cards 卡牌ID数组（引用传递，原地排序）
 */
function sortCardsDesc(array &$cards): void
{
    usort($cards, function (int $a, int $b): int {
        $wA = getCardWeight($a);
        $wB = getCardWeight($b);
        // 权重大的在前
        if ($wA !== $wB) {
            return $wB - $wA;
        }
        // 同权重按ID排序（保持稳定）
        return $a - $b;
    });
}

/**
 * 判断两张牌权重是否连续（顺子检查用）
 * @param int $w1 权重1
 * @param int $w2 权重2
 * @return bool
 */
function isConsecutiveWeight(int $w1, int $w2): bool
{
    return ($w1 - 1) === $w2;
}

/**
 * 生成一副完整的54张牌
 * @return array<int> 0-53 的乱序数组
 */
function createShuffledDeck(): array
{
    $deck = range(0, 53);
    shuffle($deck);
    return $deck;
}

/**
 * 卡牌ID转显示名称（调试用）
 * @param int $cardId
 * @return string
 */
function cardName(int $cardId): string
{
    if ($cardId === 0) return '小王';
    if ($cardId === 1) return '大王';
    $weight = getCardWeight($cardId);
    $names = [
        15 => 'A', 14 => 'K', 13 => 'Q', 12 => 'J',
        11 => '10', 10 => '9', 9 => '8', 8 => '7',
        7 => '6', 6 => '5', 5 => '4', 4 => '3', 3 => '2',
    ];
    return $names[$weight] ?? "?({$weight})";
}

/**
 * 获取初始状态数组
 * @return string JSON 编码的状态数组
 */
function initialState(): string
{
    return json_encode([STATE_CALL_LANDLORD, STATE_IDLE, STATE_IDLE]);
}
