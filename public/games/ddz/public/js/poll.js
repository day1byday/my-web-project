/**
 * DDZ 斗地主 - 轮询管理器
 * 管理房间和游戏状态的定时轮询
 * 
 * 优化: 轮询间隔从 100ms 提升到 500ms (config.php)
 */
var PollManager = (function () {
    'use strict';

    var INTERVAL = 500; // ms, 与 config.php 保持一致
    var timers = {};

    /**
     * 启动一个轮询任务
     * @param {string}   name     - 任务名称（用于去重）
     * @param {Function} callback - 轮询回调
     * @param {number}   [interval] - 自定义间隔（ms）
     */
    function start(name, callback, interval) {
        stop(name);
        var ms = interval || INTERVAL;
        // 立即执行一次
        callback();
        timers[name] = setInterval(callback, ms);
    }

    /**
     * 停止轮询任务
     */
    function stop(name) {
        if (timers[name]) {
            clearInterval(timers[name]);
            delete timers[name];
        }
    }

    /**
     * 停止所有轮询
     */
    function stopAll() {
        for (var name in timers) {
            clearInterval(timers[name]);
        }
        timers = {};
    }

    /**
     * 检查是否有活跃的轮询
     */
    function isRunning(name) {
        return !!timers[name];
    }

    return {
        start: start,
        stop: stop,
        stopAll: stopAll,
        isRunning: isRunning
    };
})();
