<?php
/**
 * DDZ 斗地主 - 卡牌检测算法验证测试
 * 验证 detectCardType() 的输出与原 200 行 if-else 链完全一致
 * 
 * 卡牌ID → 权重映射:
 *   0=小王(17), 1=大王(16)
 *   2-5=A(15), 6-9=K(14), 10-13=Q(13), 14-17=J(12),
 *   18-21=10(11), 22-25=9(10), 26-29=8(9), 30-33=7(8),
 *   34-37=6(7), 38-41=5(6), 42-45=4(5), 46-49=3(4),
 *   50-53=2(3)
 * 
 * 用法: php tests/card_detector_test.php
 */

require_once __DIR__ . '/../lib/cards.php';
require_once __DIR__ . '/../lib/card_detector.php';

$passed = 0;
$failed = 0;

function test(string $name, array $cards, int $expectedType, int $expectedWeight, bool $expectValid = true): void
{
    global $passed, $failed;
    $result = detectCardType($cards);
    
    if (!$expectValid) {
        // 错误牌型：只检查 valid=false，type/weight 不做严格要求
        $ok = $result['valid'] === false;
    } else {
        $ok = $result['valid'] && $result['type'] === $expectedType && $result['weight'] === $expectedWeight;
    }
    echo ($ok ? "[PASS]" : "[FAIL]") . " $name" . PHP_EOL;
    if (!$ok) {
        echo "  Expected: type={$expectedType}, weight={$expectedWeight}" . PHP_EOL;
        echo "  Got:      type={$result['type']}, weight={$result['weight']}, valid={$result['valid']}" . PHP_EOL;
        $failed++;
    } else {
        $passed++;
    }
}

echo "========================================" . PHP_EOL;
echo " 卡牌检测算法验证测试" . PHP_EOL;
echo "========================================" . PHP_EOL . PHP_EOL;

// ── 1张: 单张 (type=1) ──
test("单张-A",  [2], 1, 15);       // A
test("单张-K",  [6], 1, 14);       // K
test("单张-2",  [50], 1, 3);       // 2

// ── 2张 ──
test("对子-A",  [2,3], 2, 15);     // 对A
test("对子-3",  [47,48], 2, 4);    // 对3
test("王炸",    [0,1], 0, 99);     // 小王+大王

// ── 3张: 三不带 (type=3) ──
test("三不带-A", [2,3,4], 3, 15);
test("三不带-2", [50,51,52], 3, 3);

// ── 4张 ──
test("炸弹-A",   [2,3,4,5], 4, 15);          // 炸弹
test("三带一",   [2,3,4,50], 5, 15);         // AAA带2

// ── 5张 ──
test("5顺-A~10", [2,6,10,14,18], 6, 15);     // A-K-Q-J-10 (权重15-14-13-12-11)
test("三带一对", [2,3,4,50,51], 7, 15);      // AAA+22

// ── 6张 ──
test("6顺",     [2,6,10,14,18,22], 8, 15);   // A-K-Q-J-10-9
test("连对×3",  [2,3,6,7,10,11], 9, 15);     // AA-KK-QQ
test("飞机×2",  [2,3,4,6,7,8], 10, 14);      // AAA-KKK (不带) → weight=14
test("四带二",  [2,3,4,5,6,7], 11, 15);      // AAAA+KK → weight=15

// ── 7张: 7顺 (type=12) ──
test("7顺",     [2,6,10,14,18,22,26], 12, 15);

// ── 8张 ──
test("8顺",     [2,6,10,14,18,22,26,30], 13, 15);
test("连对×4",  [2,3,6,7,10,11,14,15], 15, 15);  // AA-KK-QQ-JJ → max weight=15 (AA)

// ── 9张 ──
test("9顺",     [2,6,10,14,18,22,26,30,34], 16, 15);
test("飞机×3",  [2,3,4,6,7,8,10,11,12], 17, 13); // AAA-KKK-QQQ → min weight=13

// ── 10张 ──
test("10顺",    [2,6,10,14,18,22,26,30,34,38], 18, 15);

// ── 11张: 11顺 (type=20) ──
test("11顺",    [2,6,10,14,18,22,26,30,34,38,42], 20, 15);

// ── 12张 ──
test("12顺",    [2,6,10,14,18,22,26,30,34,38,42,46], 21, 15);

// ── 14张: 7连对 (type=23) ──
$cards14 = [2,3, 6,7, 10,11, 14,15, 18,19, 22,23, 26,27]; // AA,KK,QQ,JJ,1010,99,88
test("连对×7",  $cards14, 23, 15);  // AA is highest → max weight=15

// ── 错误牌型 ──
echo PHP_EOL . "--- 错误牌型测试 ---" . PHP_EOL;
test("错误-2张不同", [2,6], -1, -1, false);
test("错误-3张不同", [2,6,10], -1, -1, false);
test("错误-13张",    array_merge(range(0,3), range(6,9), range(10,13), [14,15,16,17,18]), -1, -1, false);

echo PHP_EOL . "========================================" . PHP_EOL;
echo " 结果: {$passed} 通过, {$failed} 失败" . PHP_EOL;
echo "========================================" . PHP_EOL;

exit($failed > 0 ? 1 : 0);
