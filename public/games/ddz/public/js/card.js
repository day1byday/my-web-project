/**
 * DDZ 斗地主 - 客户端卡牌类型检测算法
 * 与 PHP lib/card_detector.php 完全一致的算法实现
 * 
 * 用于客户端即时反馈牌型，实际出牌以服务端 validate 为准
 */
var CardDetector = (function () {
    'use strict';

    // 权重表: 54张牌，索引即卡牌ID
    var CARD_WEIGHT = [
        17, 16,                   // 0=小王, 1=大王
        15,15,15,15,              // 2-5: A
        14,14,14,14,              // 6-9: K
        13,13,13,13,              // 10-13: Q
        12,12,12,12,              // 14-17: J
        11,11,11,11,              // 18-21: 10
        10,10,10,10,              // 22-25: 9
         9, 9, 9, 9,              // 26-29: 8
         8, 8, 8, 8,              // 30-33: 7
         7, 7, 7, 7,              // 34-37: 6
         6, 6, 6, 6,              // 38-41: 5
         5, 5, 5, 5,              // 42-45: 4
         4, 4, 4, 4,              // 46-49: 3
         3, 3, 3, 3               // 50-53: 2
    ];

    function getWeight(cardId) {
        return CARD_WEIGHT[cardId] || 0;
    }

    /**
     * 构建频率分布: weight => 出现次数
     */
    function buildFreq(weights) {
        var freq = {};
        for (var i = 0; i < weights.length; i++) {
            var w = weights[i];
            freq[w] = (freq[w] || 0) + 1;
        }
        return freq;
    }

    /**
     * 检查权重数组是否连续递减
     */
    function isConsecutiveDescending(weights) {
        for (var i = 0; i < weights.length - 1; i++) {
            if (weights[i] - 1 !== weights[i + 1]) return false;
        }
        return true;
    }

    /**
     * 检查是否为连对模式
     */
    function isDoubleStraightPattern(freq, expectedPairs, minWeight) {
        var keys = Object.keys(freq).map(Number);
        if (keys.length !== expectedPairs) return false;
        for (var i = 0; i < keys.length; i++) {
            if (freq[keys[i]] !== 2) return false;
        }
        keys.sort(function (a, b) { return a - b; });
        if (keys[0] <= minWeight) return false;
        for (var i = 0; i < keys.length - 1; i++) {
            if (keys[i] + 1 !== keys[i + 1]) return false;
        }
        return true;
    }

    function maxKey(freq) {
        var keys = Object.keys(freq).map(Number);
        return Math.max.apply(null, keys);
    }

    function minWeight(weights) {
        return Math.min.apply(null, weights);
    }

    // ── 各牌型检测 ──

    function detectSingle(weights) {
        return { type: 1, weight: weights[0], valid: true };
    }

    function detectPair(weights, freq) {
        var keys = Object.keys(freq);
        if (keys.length === 1) {
            return { type: 2, weight: weights[0], valid: true };
        }
        // 王炸
        if (freq[17] === 1 && freq[16] === 1) {
            return { type: 0, weight: 99, valid: true };
        }
        return { type: -1, weight: -1, valid: false };
    }

    function detectTriple(weights, freq) {
        if (Object.keys(freq).length === 1) {
            return { type: 3, weight: weights[0], valid: true };
        }
        return { type: -1, weight: -1, valid: false };
    }

    function detectFour(weights, freq) {
        var keys = Object.keys(freq).map(Number);
        var vals = keys.map(function (k) { return freq[k]; });

        // 炸弹
        if (keys.length === 1) {
            return { type: 4, weight: weights[0], valid: true };
        }

        // 三带一
        if (keys.length === 2) {
            if ((vals[0] === 3 && vals[1] === 1) || (vals[0] === 1 && vals[1] === 3)) {
                var tripleW = vals[0] === 3 ? keys[0] : keys[1];
                return { type: 5, weight: tripleW, valid: true };
            }
        }

        return { type: -1, weight: -1, valid: false };
    }

    function detectFive(weights, freq) {
        // 顺子(5张)
        if (isConsecutiveDescending(weights) && minWeight(weights) >= 4) {
            return { type: 6, weight: weights[0], valid: true };
        }

        // 三带一对
        var keys = Object.keys(freq).map(Number);
        var vals = keys.map(function (k) { return freq[k]; });
        if (keys.length === 2) {
            if ((vals[0] === 3 && vals[1] === 2) || (vals[0] === 2 && vals[1] === 3)) {
                var tripleW = vals[0] === 3 ? keys[0] : keys[1];
                return { type: 7, weight: tripleW, valid: true };
            }
        }

        return { type: -1, weight: -1, valid: false };
    }

    function detectSix(weights, freq) {
        // 顺子(6张)
        if (isConsecutiveDescending(weights) && minWeight(weights) >= 4) {
            return { type: 8, weight: weights[0], valid: true };
        }

        // 连对(3对)
        if (isDoubleStraightPattern(freq, 3, 3)) {
            return { type: 9, weight: maxKey(freq), valid: true };
        }

        // 飞机×2不带
        var keys = Object.keys(freq).map(Number);
        var vals = keys.map(function (k) { return freq[k]; });
        if (keys.length === 2 && vals[0] === 3 && vals[1] === 3) {
            if (Math.abs(keys[0] - keys[1]) === 1) {
                return { type: 10, weight: Math.min(keys[0], keys[1]), valid: true };
            }
        }

        // 四带二
        var fourW = null;
        var hasPair = false;
        for (var k in freq) {
            if (freq[k] === 4) fourW = Number(k);
            if (freq[k] === 2) hasPair = true;
        }
        if (fourW !== null && hasPair) {
            return { type: 11, weight: fourW, valid: true };
        }

        return { type: -1, weight: -1, valid: false };
    }

    function detectStraight(weights, typeNum) {
        if (isConsecutiveDescending(weights) && minWeight(weights) >= 4) {
            return { type: typeNum, weight: weights[0], valid: true };
        }
        return { type: -1, weight: -1, valid: false };
    }

    function detectEight(weights, freq) {
        if (isConsecutiveDescending(weights) && minWeight(weights) >= 4) {
            return { type: 13, weight: weights[0], valid: true };
        }

        if (isDoubleStraightPattern(freq, 4, 3)) {
            return { type: 15, weight: maxKey(freq), valid: true };
        }

        // 飞机×2带双
        var tripleKeys = [];
        for (var k in freq) {
            if (freq[k] === 3) tripleKeys.push(Number(k));
        }
        if (tripleKeys.length === 2) {
            return { type: 14, weight: Math.min(tripleKeys[0], tripleKeys[1]), valid: true };
        }

        return { type: -1, weight: -1, valid: false };
    }

    function detectNine(weights, freq) {
        if (isConsecutiveDescending(weights) && minWeight(weights) >= 4) {
            return { type: 16, weight: weights[0], valid: true };
        }

        // 飞机×3不带
        var keys = Object.keys(freq).map(Number);
        var vals = keys.map(function (k) { return freq[k]; });
        if (keys.length === 3 && vals[0] === 3 && vals[1] === 3 && vals[2] === 3) {
            keys.sort(function (a, b) { return a - b; });
            if (keys[0] + 1 === keys[1] && keys[1] + 1 === keys[2]) {
                return { type: 17, weight: keys[0], valid: true };
            }
        }

        return { type: -1, weight: -1, valid: false };
    }

    function detectTen(weights, freq) {
        if (isConsecutiveDescending(weights) && minWeight(weights) >= 4) {
            return { type: 18, weight: weights[0], valid: true };
        }
        if (isDoubleStraightPattern(freq, 5, 3)) {
            return { type: 19, weight: maxKey(freq), valid: true };
        }
        return { type: -1, weight: -1, valid: false };
    }

    function detectTwelve(weights, freq) {
        if (isConsecutiveDescending(weights) && minWeight(weights) >= 4) {
            return { type: 21, weight: weights[0], valid: true };
        }
        if (isDoubleStraightPattern(freq, 6, 3)) {
            return { type: 22, weight: maxKey(freq), valid: true };
        }
        return { type: -1, weight: -1, valid: false };
    }

    function detectDoubleStraight(freq, typeNum) {
        var pairCount = Object.keys(freq).length;
        if (isDoubleStraightPattern(freq, pairCount, 3)) {
            return { type: typeNum, weight: maxKey(freq), valid: true };
        }
        return { type: -1, weight: -1, valid: false };
    }

    /**
     * 主入口: 检测一组卡牌的类型
     * @param {number[]} cardIds - 已排序的卡牌ID数组
     * @returns {{type: number, weight: number, valid: boolean}}
     */
    function detect(cardIds) {
        var count = cardIds.length;
        if (count === 0) {
            return { type: -1, weight: -1, valid: false };
        }

        // 排序（权重降序）
        cardIds.sort(function (a, b) {
            var wa = getWeight(b) - getWeight(a);
            return wa !== 0 ? wa : a - b;
        });

        var weights = cardIds.map(getWeight);
        var freq = buildFreq(weights);

        switch (count) {
            case 1:  return detectSingle(weights);
            case 2:  return detectPair(weights, freq);
            case 3:  return detectTriple(weights, freq);
            case 4:  return detectFour(weights, freq);
            case 5:  return detectFive(weights, freq);
            case 6:  return detectSix(weights, freq);
            case 7:  return detectStraight(weights, 12);
            case 8:  return detectEight(weights, freq);
            case 9:  return detectNine(weights, freq);
            case 10: return detectTen(weights, freq);
            case 11: return detectStraight(weights, 20);
            case 12: return detectTwelve(weights, freq);
            case 14: return detectDoubleStraight(freq, 23);
            case 16: return detectDoubleStraight(freq, 24);
            case 18: return detectDoubleStraight(freq, 25);
            case 20: return detectDoubleStraight(freq, 26);
            default: return { type: -1, weight: -1, valid: false };
        }
    }

    // 公开 API
    return {
        CARD_WEIGHT: CARD_WEIGHT,
        getWeight: getWeight,
        detect: detect
    };
})();
