<?php
/**
 * DDZ 斗地主 - 卡牌类型检测算法
 * 基于频率分布分析，替代原 200 行 if-else 链
 * 
 * 修复: P2#17 (25个重复分支 → 算法), P0#3 (服务端验证), P1#14 (排序)
 * 
 * 牌型映射表 (保持与原代码完全一致):
 *   1张: type=1 (单张)
 *   2张: type=2 (对子) / type=0 (王炸, w=99)
 *   3张: type=3 (三不带)
 *   4张: type=4 (炸弹) / type=5 (三带一)
 *   5张: type=6 (5顺) / type=7 (三带一对)
 *   6张: type=8 (6顺) / type=9 (连对×3) / type=10 (飞机×2不带) / type=11 (四带二)
 *   7张: type=12 (7顺)
 *   8张: type=13 (8顺) / type=14 (飞机×2带双?) / type=15 (连对×4)
 *   9张: type=16 (9顺) / type=17 (飞机×3不带)
 *  10张: type=18 (10顺) / type=19 (连对×5)
 *  11张: type=20 (11顺)
 *  12张: type=21 (12顺) / type=22 (连对×6)
 *  14张: type=23 (连对×7)
 *  16张: type=24 (连对×8)
 *  18张: type=25 (连对×9)
 *  20张: type=26 (连对×10)
 */

require_once __DIR__ . '/cards.php';

/**
 * 检测卡牌类型
 * 
 * @param array<int> $cardIds 已排序的卡牌ID数组
 * @return array{type: int, weight: int, valid: bool}
 */
function detectCardType(array $cardIds): array
{
    $count = count($cardIds);
    if ($count === 0) {
        return ['type' => -1, 'weight' => -1, 'valid' => false];
    }

    // 确保卡牌已排序（权重降序）
    sortCardsDesc($cardIds);

    // 构建权重数组和频率分布
    $weights = array_map('getCardWeight', $cardIds);
    $freq = array_count_values($weights);  // weight => 出现次数

    $result = match ($count) {
        1  => detectSingle($weights),
        2  => detectPair($weights, $freq),
        3  => detectTriple($weights, $freq),
        4  => detectFourCards($weights, $freq),
        5  => detectFiveCards($weights, $freq),
        6  => detectSixCards($weights, $freq),
        7  => detectStraight($weights, 12),        // 7顺 → type 12
        8  => detectEightCards($weights, $freq),
        9  => detectNineCards($weights, $freq),
        10 => detectTenCards($weights, $freq),
        11 => detectStraight($weights, 20),        // 11顺 → type 20
        12 => detectTwelveCards($weights, $freq),
        14 => detectDoubleStraight($freq, 23),     // 7连对 → type 23
        16 => detectDoubleStraight($freq, 24),     // 8连对 → type 24
        18 => detectDoubleStraight($freq, 25),     // 9连对 → type 25
        20 => detectDoubleStraight($freq, 26),     // 10连对 → type 26
        default => ['type' => -1, 'weight' => -1, 'valid' => false],
    };

    return $result;
}

// ═══════════════════════════════════════════
// 各牌型检测函数
// ═══════════════════════════════════════════

/**
 * 单张 (type=1)
 */
function detectSingle(array $weights): array
{
    return ['type' => 1, 'weight' => $weights[0], 'valid' => true];
}

/**
 * 2张：对子(type=2) 或 王炸(type=0, w=99)
 */
function detectPair(array $weights, array $freq): array
{
    // 对子：两张同权重
    if (count($freq) === 1) {
        return ['type' => 2, 'weight' => $weights[0], 'valid' => true];
    }

    // 王炸：小王(17) + 大王(16)
    if (isset($freq[17]) && $freq[17] === 1 && isset($freq[16]) && $freq[16] === 1) {
        return ['type' => 0, 'weight' => 99, 'valid' => true];
    }

    return ['type' => -1, 'weight' => -1, 'valid' => false];
}

/**
 * 3张：三不带 (type=3)
 */
function detectTriple(array $weights, array $freq): array
{
    if (count($freq) === 1) {
        return ['type' => 3, 'weight' => $weights[0], 'valid' => true];
    }
    return ['type' => -1, 'weight' => -1, 'valid' => false];
}

/**
 * 4张：炸弹(type=4) 或 三带一(type=5)
 */
function detectFourCards(array $weights, array $freq): array
{
    // 炸弹：4张同权重
    if (count($freq) === 1) {
        return ['type' => 4, 'weight' => $weights[0], 'valid' => true];
    }

    // 三带一：1个三重 + 1个单张
    if (count($freq) === 2) {
        $vals = array_values($freq);
        if (($vals[0] === 3 && $vals[1] === 1) || ($vals[0] === 1 && $vals[1] === 3)) {
            // 找到三重牌的权重
            $tripleWeight = ($vals[0] === 3) ? array_keys($freq)[0] : array_keys($freq)[1];
            return ['type' => 5, 'weight' => $tripleWeight, 'valid' => true];
        }
    }

    return ['type' => -1, 'weight' => -1, 'valid' => false];
}

/**
 * 5张：5顺(type=6) 或 三带一对(type=7)
 */
function detectFiveCards(array $weights, array $freq): array
{
    // 顺子：权重连续递减，最小权重 > 5（不含2和王）
    if (isConsecutiveDescending($weights) && min($weights) >= 4) {
        return ['type' => 6, 'weight' => $weights[0], 'valid' => true];
    }

    // 三带一对：1个三重 + 1个对子
    if (count($freq) === 2) {
        $vals = array_values($freq);
        if (($vals[0] === 3 && $vals[1] === 2) || ($vals[0] === 2 && $vals[1] === 3)) {
            $tripleWeight = ($vals[0] === 3) ? array_keys($freq)[0] : array_keys($freq)[1];
            return ['type' => 7, 'weight' => $tripleWeight, 'valid' => true];
        }
    }

    return ['type' => -1, 'weight' => -1, 'valid' => false];
}

/**
 * 6张：6顺(type=8) / 连对×3(type=9) / 飞机×2不带(type=10) / 四带二(type=11)
 */
function detectSixCards(array $weights, array $freq): array
{
    // 顺子(6张)：权重连续，最小>5
    if (isConsecutiveDescending($weights) && min($weights) >= 4) {
        return ['type' => 8, 'weight' => $weights[0], 'valid' => true];
    }

    // 连对(3对)：所有频率=2，权重连续，最小>5
    if (isDoubleStraightPattern($freq, 3, 3)) {
        $maxWeight = max(array_keys($freq));
        return ['type' => 9, 'weight' => $maxWeight, 'valid' => true];
    }

    // 飞机×2不带：2个连续三重
    if (count($freq) === 2) {
        $keys = array_keys($freq);
        $vals = array_values($freq);
        if ($vals[0] === 3 && $vals[1] === 3) {
            if (abs($keys[0] - $keys[1]) === 1) {
                return ['type' => 10, 'weight' => min($keys), 'valid' => true];
            }
        }
    }

    // 四带二：1个四重 + 1个对子
    $hasFour = false;
    $fourWeight = 0;
    foreach ($freq as $w => $c) {
        if ($c === 4) { $hasFour = true; $fourWeight = $w; break; }
    }
    $hasPair = false;
    foreach ($freq as $w => $c) {
        if ($c === 2) { $hasPair = true; break; }
    }
    if ($hasFour && $hasPair) {
        return ['type' => 11, 'weight' => $fourWeight, 'valid' => true];
    }

    return ['type' => -1, 'weight' => -1, 'valid' => false];
}

/**
 * 通用顺子检测 (7-12张)
 * @param array $weights
 * @param int   $typeNum 对应的类型编号
 */
function detectStraight(array $weights, int $typeNum): array
{
    if (isConsecutiveDescending($weights) && min($weights) >= 4) {
        return ['type' => $typeNum, 'weight' => $weights[0], 'valid' => true];
    }
    return ['type' => -1, 'weight' => -1, 'valid' => false];
}

/**
 * 8张：8顺(type=13) / 飞机特殊(type=14) / 连对×4(type=15)
 */
function detectEightCards(array $weights, array $freq): array
{
    // 顺子(8张)
    if (isConsecutiveDescending($weights) && min($weights) >= 4) {
        return ['type' => 13, 'weight' => $weights[0], 'valid' => true];
    }

    // 连对(4对)
    if (isDoubleStraightPattern($freq, 4, 3)) {
        $maxWeight = max(array_keys($freq));
        return ['type' => 15, 'weight' => $maxWeight, 'valid' => true];
    }

    // 飞机×2带双(原 type=14): 2个三重 + 1个对子
    // 频率分布为 {w1:3, w2:3, w3:2} 或 {w1:3, w2:3, w3:1, w4:1}
    $tripleCount = 0;
    $tripleWeights = [];
    foreach ($freq as $w => $c) {
        if ($c === 3) {
            $tripleCount++;
            $tripleWeights[] = $w;
        }
    }
    if ($tripleCount === 2 && count($freq) >= 2) {
        // 简单判断：有2个三重即可，原始代码只检查模式匹配
        return ['type' => 14, 'weight' => min($tripleWeights), 'valid' => true];
    }

    return ['type' => -1, 'weight' => -1, 'valid' => false];
}

/**
 * 9张：9顺(type=16) / 飞机×3不带(type=17)
 */
function detectNineCards(array $weights, array $freq): array
{
    // 顺子(9张)
    if (isConsecutiveDescending($weights) && min($weights) >= 4) {
        return ['type' => 16, 'weight' => $weights[0], 'valid' => true];
    }

    // 飞机×3不带：3个连续三重
    if (count($freq) === 3) {
        $keys = array_keys($freq);
        $vals = array_values($freq);
        if ($vals[0] === 3 && $vals[1] === 3 && $vals[2] === 3) {
            sort($keys);
            if ($keys[0] + 1 === $keys[1] && $keys[1] + 1 === $keys[2]) {
                return ['type' => 17, 'weight' => $keys[0], 'valid' => true];
            }
        }
    }

    return ['type' => -1, 'weight' => -1, 'valid' => false];
}

/**
 * 10张：10顺(type=18) / 连对×5(type=19)
 */
function detectTenCards(array $weights, array $freq): array
{
    if (isConsecutiveDescending($weights) && min($weights) >= 4) {
        return ['type' => 18, 'weight' => $weights[0], 'valid' => true];
    }
    if (isDoubleStraightPattern($freq, 5, 3)) {
        return ['type' => 19, 'weight' => max(array_keys($freq)), 'valid' => true];
    }
    return ['type' => -1, 'weight' => -1, 'valid' => false];
}

/**
 * 12张：12顺(type=21) / 连对×6(type=22)
 */
function detectTwelveCards(array $weights, array $freq): array
{
    if (isConsecutiveDescending($weights) && min($weights) >= 4) {
        return ['type' => 21, 'weight' => $weights[0], 'valid' => true];
    }
    if (isDoubleStraightPattern($freq, 6, 3)) {
        return ['type' => 22, 'weight' => max(array_keys($freq)), 'valid' => true];
    }
    return ['type' => -1, 'weight' => -1, 'valid' => false];
}

/**
 * 通用连对检测
 * @param array $freq 频率分布
 * @param int   $typeNum 类型编号
 * @return array
 */
function detectDoubleStraight(array $freq, int $typeNum): array
{
    $pairCount = count($freq);
    if (isDoubleStraightPattern($freq, $pairCount, 3)) {
        return ['type' => $typeNum, 'weight' => max(array_keys($freq)), 'valid' => true];
    }
    return ['type' => -1, 'weight' => -1, 'valid' => false];
}

// ═══════════════════════════════════════════
// 辅助判断函数
// ═══════════════════════════════════════════

/**
 * 检查权重数组是否连续递减 (w[i] - 1 == w[i+1])
 * 即：大 → 小，逐个减1
 */
function isConsecutiveDescending(array $weights): bool
{
    $len = count($weights);
    if ($len < 2) return true;

    for ($i = 0; $i < $len - 1; $i++) {
        if ($weights[$i] - 1 !== $weights[$i + 1]) {
            return false;
        }
    }
    return true;
}

/**
 * 检查是否为连对模式
 * @param array $freq       频率分布
 * @param int   $expectedPairs 期望的对子数量
 * @param int   $minWeight   最小允许的权重值（排除2和王）
 * @return bool
 */
function isDoubleStraightPattern(array $freq, int $expectedPairs, int $minWeight): bool
{
    // 频率分布应该有 $expectedPairs 个不同权重
    if (count($freq) !== $expectedPairs) {
        return false;
    }

    // 每个权重的出现次数都应该是2
    foreach ($freq as $count) {
        if ($count !== 2) return false;
    }

    // 权重必须连续
    $keys = array_keys($freq);
    sort($keys);

    if (min($keys) <= $minWeight) return false;

    for ($i = 0; $i < count($keys) - 1; $i++) {
        if ($keys[$i] + 1 !== $keys[$i + 1]) {
            return false;
        }
    }

    return true;
}
