/**
 * DDZ 斗地主 - API 调用封装
 * 统一管理所有后端 API 请求
 */
var DdzApi = (function () {
    'use strict';

    // 自动计算 base URL
    var baseUrl = (function () {
        var path = window.location.pathname;
        // 移除 public/ 及之后的部分
        var idx = path.indexOf('/public/');
        if (idx !== -1) path = path.substring(0, idx);
        // 确保是绝对路径
        if (path.charAt(path.length - 1) !== '/') path += '/';
        return path;
    })();

    /**
     * 通用 GET 请求
     */
    function get(endpoint, params) {
        var url = baseUrl + endpoint;
        if (params) {
            var qs = Object.keys(params)
                .filter(function (k) { return params[k] !== null && params[k] !== undefined; })
                .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); })
                .join('&');
            if (qs) url += '?' + qs;
        }
        return $.ajax({ method: 'GET', url: url, dataType: 'json' });
    }

    /**
     * 通用 POST 请求
     */
    function post(endpoint, data) {
        return $.ajax({
            method: 'POST',
            url: baseUrl + endpoint,
            contentType: 'application/json',
            data: JSON.stringify(data || {}),
            dataType: 'json'
        });
    }

    // ═══════════════════════════════════════
    // 公开 API 方法
    // ═══════════════════════════════════════

    return {
        baseUrl: baseUrl,

        // ── 玩家 ──
        /** 获取当前登录玩家档案 */
        getMyProfile: function () {
            return get('api/player/me.php');
        },

        /** 获取指定玩家游戏信息 */
        getPlayer: function (playerId) {
            return get('api/findplayer/', { id: playerId });
        },

        /** 获取下家信息 */
        getNextPlayer: function (playerId, playerList) {
            return get('api/findplayer/next/', { id: playerId, player: playerList });
        },

        /** 获取上家信息 */
        getPrevPlayer: function (playerId, playerList) {
            return get('api/findplayer/up/', { id: playerId, player: playerList });
        },

        // ── 房间 ──
        /** 创建房间 */
        createRoom: function () {
            return get('createroom.php?n=1');
        },

        /** 获取房间信息 */
        getRoom: function (roomId) {
            return get('api/findroom/', { roomid: roomId });
        },

        /** 加入房间 */
        joinRoom: function (roomId) {
            return get('api/room/', { roomid: roomId });
        },

        /** 退出房间 */
        leaveRoom: function () {
            return get('roomout.php');
        },

        // ── 游戏操作 ──
        /** 准备 */
        ready: function () {
            return get('ready.php');
        },

        /** 开始游戏（房主） */
        beginGame: function (playerIds, roomId) {
            return get('api/deal/index.php', {
                id1: playerIds[0],
                id2: playerIds[1],
                id3: playerIds[2],
                roomid: roomId
            });
        },

        /** 叫地主/抢地主 */
        callLandlord: function (id, id2, id3, roomId, prepare) {
            return get('api/up/', {
                id: id, id2: id2, id3: id3,
                roomid: roomId, prepare: prepare
            });
        },

        /** 不叫 */
        passLandlord: function (roomId, prepare, id) {
            return get('api/bujiao/', {
                roomid: roomId, prepare: prepare, id: id
            });
        },

        /** 出牌 */
        playCards: function (roomId, prepare, id, cardIndices, weight, type) {
            return get('api/play/', {
                roomid: roomId,
                prepare: prepare,
                id: id,
                num: JSON.stringify(cardIndices),
                weight: weight,
                type: type
            });
        },

        /** 不出 */
        passTurn: function (roomId, prepare) {
            return get('api/noplay/', {
                roomid: roomId, prepare: prepare
            });
        },

        /** 获取游戏状态 */
        getGameState: function (id, roomId) {
            return get('api/state/', { id: id, roomid: roomId });
        },

        // ── 轮询 ──
        /** 房间轮询 */
        pollRoom: function () {
            return get('server.php');
        },

        /** 游戏轮询 */
        pollGame: function (playerList, id, prepare, roomId) {
            return get('gameserver.php', {
                player: playerList,
                id: id,
                prepare: prepare,
                roomid: roomId
            });
        }
    };
})();
