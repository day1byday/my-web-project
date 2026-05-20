/**
 * DDZ 斗地主 - 游戏状态机模块
 * 管理游戏数据加载、UI 渲染、操作处理和轮询
 *
 * 依赖: DdzApi, DdzUI, CardDetector, PollManager, jQuery
 */
var GameActions = (function () {
    'use strict';

    // ═══════════════════════════════════════
    // 全局状态
    // ═══════════════════════════════════════

    var g = {
        myId: 0,
        myName: '',
        roomId: '',

        // 3 个玩家的 ID（房间的 player JSON 数组）
        players: [0, 0, 0],

        // 我的手牌（卡牌 ID 数组）
        myCards: [],

        // 地主底牌
        overCards: [],

        // 弃牌堆
        discard: [],

        // 房间 prepare 序列化字符串（用于变更检测）
        prepare: '',

        // 我的游戏状态: 1=叫地主, 2=抢地主, 3=自由出牌, 4=应牌, 5=强制过牌
        state: 0,

        // 我在 players 数组中的位置索引
        myEx: -1,

        // 缓存玩家信息: { playerId: { name, card_count, touxiang, identity } }
        playerInfo: {},

        // 加载状态标记
        loaded: false
    };

    // ═══════════════════════════════════════
    // 工具函数
    // ═══════════════════════════════════════

    /** 从 URL 获取 roomId */
    function getRoomIdFromUrl() {
        var m = window.location.search.match(/roomid=(\d+)/);
        return m ? m[1] : '';
    }

    /** 显示错误提示 */
    function showError(msg) {
        DdzUI.showToast(msg, 2500);
    }

    /** 解析 API 返回的卡牌 JSON 字符串 */
    function parseCards(cardStr) {
        if (!cardStr || cardStr === '[]') return [];
        try {
            var arr = JSON.parse(cardStr);
            return Array.isArray(arr) ? arr : [];
        } catch (e) {
            // 可能是 "[1,2,3]" 格式（两边带多余空格）
            var cleaned = cardStr.replace(/[\[\]\s]/g, '');
            if (!cleaned) return [];
            return cleaned.split(',').map(function (s) { return parseInt(s, 10); });
        }
    }

    // ═══════════════════════════════════════
    // 数据加载
    // ═══════════════════════════════════════

    /** 加载所有初始数据 */
    function loadGame() {
        g.roomId = getRoomIdFromUrl();
        if (!g.roomId) {
            showError('缺少房间号参数');
            return;
        }

        // 第一步：获取自己的 ID
        DdzApi.getMyProfile()
            .done(function (data) {
                if (data.code === 200) {
                    g.myId = data.id;
                    g.myName = data.name;
                    loadRoomData();
                } else if (data.code === 401) {
                    window.location.href = 'login.html';
                } else {
                    showError(data.message || '获取玩家信息失败');
                }
            })
            .fail(function () {
                showError('网络错误，请刷新重试');
            });
    }

    /** 加载房间数据 */
    function loadRoomData() {
        DdzApi.getRoom(g.roomId)
            .done(function (data) {
                if (data.code === 200) {
                    // 解析 players 数组
                    if (typeof data.player === 'string') {
                        g.players = JSON.parse(data.player);
                    } else if (Array.isArray(data.player)) {
                        g.players = data.player;
                    }

                    // 解析 prepare
                    g.prepare = typeof data.prepare === 'string' ? data.prepare : JSON.stringify(data.prepare);

                    // 解析弃牌堆
                    if (data.discard && data.discard !== '' && data.discard !== '[]') {
                        g.discard = parseCards(data.discard);
                    } else {
                        g.discard = [];
                    }

                    // 找到我的位置
                    for (var i = 0; i < 3; i++) {
                        if (parseInt(g.players[i]) === g.myId) {
                            g.myEx = i;
                            break;
                        }
                    }

                    if (g.myEx === -1) {
                        showError('你不在该房间中');
                        return;
                    }

                    // 加载玩家详情
                    loadAllPlayers();
                } else {
                    showError(data.message || '房间不存在');
                }
            })
            .fail(function () {
                showError('获取房间信息失败');
            });
    }

    /** 加载所有玩家游戏信息 */
    function loadAllPlayers() {
        var loaded = 0;
        var totalToLoad = 3;

        function checkDone() {
            loaded++;
            if (loaded >= totalToLoad) {
                loadGameState();
            }
        }

        // 加载我自己
        DdzApi.getPlayer(g.myId)
            .done(function (data) {
                if (data.code === 200) {
                    g.playerInfo[g.myId] = {
                        name: data.name,
                        touxiang: data.touxiang || '',
                        identity: data.identity || '',
                        card_count: 0
                    };
                    g.myCards = parseCards(data.card);
                    g.overCards = parseCards(data.overcard);
                    g.playerInfo[g.myId].card_count = g.myCards.length;
                }
                checkDone();
            })
            .fail(function () { checkDone(); });

        // 加载下家
        var nextId = getNextPlayerId();
        if (nextId) {
            DdzApi.getNextPlayer(g.myId, JSON.stringify(g.players))
                .done(function (data) {
                    if (data.code === 200) {
                        g.playerInfo[nextId] = {
                            name: data.name,
                            touxiang: data.touxiang || '',
                            identity: data.identity || '',
                            card_count: parseCards(data.card).length
                        };
                    }
                    checkDone();
                })
                .fail(function () { checkDone(); });
        } else {
            checkDone();
        }

        // 加载上家
        var prevId = getPrevPlayerId();
        if (prevId) {
            DdzApi.getPrevPlayer(g.myId, JSON.stringify(g.players))
                .done(function (data) {
                    if (data.code === 200) {
                        g.playerInfo[prevId] = {
                            name: data.name,
                            touxiang: data.touxiang || '',
                            identity: data.identity || '',
                            card_count: parseCards(data.card).length
                        };
                    }
                    checkDone();
                })
                .fail(function () { checkDone(); });
        } else {
            checkDone();
        }
    }

    /** 加载游戏状态 */
    function loadGameState() {
        DdzApi.getGameState(g.myId, g.roomId)
            .done(function (data) {
                if (data.code === 200) {
                    g.state = parseInt(data.state) || 0;
                }
                g.loaded = true;
                renderAll();
                startPolling();
            })
            .fail(function () {
                g.loaded = true;
                renderAll();
                startPolling();
            });
    }

    /** 获取下家 ID */
    function getNextPlayerId() {
        // players: [me, player2, player3] 或 [player1, me, player3] 等
        // "下家" 是顺时针下一个，即数组中索引 +1（循环）
        var nextEx = (g.myEx + 1) % 3;
        return parseInt(g.players[nextEx]) || 0;
    }

    /** 获取上家 ID */
    function getPrevPlayerId() {
        var prevEx = (g.myEx + 2) % 3;
        return parseInt(g.players[prevEx]) || 0;
    }

    /** 获取下家信息 */
    function getNextPlayerInfo() {
        var id = getNextPlayerId();
        return g.playerInfo[id] || null;
    }

    /** 获取上家信息 */
    function getPrevPlayerInfo() {
        var id = getPrevPlayerId();
        return g.playerInfo[id] || null;
    }

    /** 获取我的信息 */
    function getMyInfo() {
        return g.playerInfo[g.myId] || { name: g.myName, touxiang: '', identity: '' };
    }

    // ═══════════════════════════════════════
    // UI 渲染
    // ═══════════════════════════════════════

    /** 渲染所有 UI */
    function renderAll() {
        renderMyCards();
        renderMyInfo();
        renderNextInfo();
        renderPrevInfo();
        renderLandlordCards();
        renderDiscard();
        renderActions();
    }

    /** 渲染我的手牌 */
    function renderMyCards() {
        DdzUI.renderHand('my-hand', g.myCards, function (cardId, index, selectedIndices) {
            // 选择变化回调 — 无需额外处理，DdzUI 内部管理状态
        });
    }

    /** 渲染我的信息标签 */
    function renderMyInfo() {
        var info = getMyInfo();
        DdzUI.renderPlayerTag('my-info', {
            name: info.name,
            touxiang: info.touxiang,
            identity: info.identity,
            card_count: g.myCards.length
        }, 'bottom');
    }

    /** 渲染下家信息标签 */
    function renderNextInfo() {
        var info = getNextPlayerInfo();
        if (info) {
            DdzUI.renderPlayerTag('next-info', info, 'right');
        }
    }

    /** 渲染上家信息标签 */
    function renderPrevInfo() {
        var info = getPrevPlayerInfo();
        if (info) {
            DdzUI.renderPlayerTag('prev-info', info, 'left');
        }
    }

    /** 渲染地主底牌 */
    function renderLandlordCards() {
        DdzUI.renderBottomCards('landlord-cards', g.overCards);
    }

    /** 渲染弃牌堆 */
    function renderDiscard() {
        DdzUI.renderDiscard('discard-pile', g.discard);
    }

    /** 渲染功能区按钮 */
    function renderActions() {
        DdzUI.renderActions('action-area', g.state);
    }

    // ═══════════════════════════════════════
    // 轮询
    // ═══════════════════════════════════════

    function startPolling() {
        PollManager.start('game', function () {
            DdzApi.pollGame(
                JSON.stringify(g.players),
                g.myId,
                g.prepare,
                g.roomId
            ).done(function (res) {
                // gameserver.php 返回纯文本: "1" / "-1" / "-2"
                var code = parseInt(res);
                if (code === -1) {
                    // 状态变化，刷新页面
                    window.location.reload();
                } else if (code === -2) {
                    // 游戏结束
                    showError('游戏结束！');
                    PollManager.stop('game');
                    setTimeout(function () {
                        window.location.href = 'room.html';
                    }, 1500);
                }
                // code === 1 → 无变化
            }).fail(function () {
                // 轮询失败静默处理
            });
        });
    }

    // ═══════════════════════════════════════
    // 游戏操作
    // ═══════════════════════════════════════

    /** 叫地主 / 抢地主 */
    function callLandlord() {
        var nextId = getNextPlayerId();
        var prevId = getPrevPlayerId();

        DdzApi.callLandlord(g.myId, nextId, prevId, g.roomId, g.prepare)
            .done(function (res) {
                if (res.code === 200) {
                    window.location.reload();
                } else {
                    showError(res.message || '操作失败');
                }
            })
            .fail(function () {
                showError('网络错误');
            });
    }

    /** 不叫 / 不抢 */
    function passLandlord() {
        DdzApi.passLandlord(g.roomId, g.prepare, g.myId)
            .done(function (res) {
                if (res.code === 200) {
                    window.location.reload();
                } else {
                    showError(res.message || '操作失败');
                }
            })
            .fail(function () {
                showError('网络错误');
            });
    }

    /** 出牌 */
    function playCards() {
        var selectedIndices = DdzUI.getSelectedIndices('my-hand');

        if (selectedIndices.length === 0) {
            showError('请选择要出的牌');
            return;
        }

        // 将选择索引转为卡牌 ID
        var selectedCardIds = selectedIndices.map(function (idx) {
            return g.myCards[idx];
        });

        // 客户端牌型检测
        var result = CardDetector.detect(selectedCardIds);
        if (!result.valid) {
            showError('错误牌型！');
            return;
        }

        DdzApi.playCards(
            g.roomId,
            g.prepare,
            g.myId,
            selectedIndices,          // 卡牌索引（不是 ID）
            result.weight,
            result.type
        ).done(function (res) {
            if (res.code === 200) {
                DdzUI.clearSelection('my-hand');
                window.location.reload();
            } else {
                showError(res.message || '出牌失败');
            }
        }).fail(function () {
            showError('网络错误');
        });
    }

    /** 不出 / 要不起 */
    function passTurn() {
        DdzApi.passTurn(g.roomId, g.prepare)
            .done(function (res) {
                if (res.code === 200) {
                    window.location.reload();
                } else {
                    showError(res.message || '操作失败');
                }
            })
            .fail(function () {
                showError('网络错误');
            });
    }

    // ═══════════════════════════════════════
    // 启动
    // ═══════════════════════════════════════

    $(function () {
        loadGame();
    });

    // 页面离开时停止轮询
    $(window).on('beforeunload', function () {
        PollManager.stopAll();
    });

    // ═══════════════════════════════════════
    // 公开接口（供 UI 按钮 onclick 调用）
    // ═══════════════════════════════════════

    return {
        callLandlord: callLandlord,
        passLandlord: passLandlord,
        playCards: playCards,
        passTurn: passTurn
    };
})();
