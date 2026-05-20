/**
 * DDZ 斗地主 - UI 渲染模块
 * 卡牌渲染、选择管理、信息展示、错误提示
 */
var DdzUI = (function () {
    'use strict';

    // ── Toast 提示 ──

    function showToast(msg, duration) {
        var toast = document.getElementById('toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toast';
            toast.className = 'error-toast';
            document.body.appendChild(toast);
        }
        toast.textContent = msg;
        toast.style.display = 'block';
        clearTimeout(toast._timer);
        toast._timer = setTimeout(function () {
            toast.style.display = 'none';
        }, duration || 2000);
    }

    // ── 卡牌渲染 ──

    /**
     * 在指定容器中渲染手牌
     * @param {string}   containerId - HTML 容器 ID
     * @param {number[]} hand        - 卡牌ID数组
     * @param {Function} [onClick]   - 点击回调 (cardId, index)
     */
    function renderHand(containerId, hand, onClick) {
        var container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '';

        var baseLeft = 17; // %
        var stepLeft = 3;  // %
        var isSelected = {};
        var selectedIndices = [];

        for (var i = 0; i < hand.length; i++) {
            var cardId = hand[i];
            var img = document.createElement('img');
            img.id = 'card-' + i;
            img.src = 'img/' + cardId + '.png';
            img.width = 120;
            img.height = 180;
            img.style.cssText = 'margin-left:-55px;position:absolute;bottom:8%;left:' + (baseLeft + i * stepLeft) + '%;cursor:pointer;transition:bottom 0.15s ease;';
            img.setAttribute('data-card-id', cardId);
            img.setAttribute('data-index', i);

            if (onClick) {
                img.onclick = function () {
                    var idx = parseInt(this.getAttribute('data-index'));
                    if (isSelected[idx]) {
                        // 取消选择
                        isSelected[idx] = false;
                        this.style.bottom = '8%';
                        var pos = selectedIndices.indexOf(idx);
                        if (pos !== -1) selectedIndices.splice(pos, 1);
                    } else {
                        // 选择
                        isSelected[idx] = true;
                        this.style.bottom = '10%';
                        selectedIndices.push(idx);
                    }
                    if (typeof onClick === 'function') {
                        onClick(parseInt(this.getAttribute('data-card-id')), idx, selectedIndices);
                    }
                };
            }

            container.appendChild(img);
        }

        // 保存状态引用
        container._handState = {
            isSelected: isSelected,
            selectedIndices: selectedIndices,
            getSelectedCards: function () {
                return selectedIndices.slice().sort(function (a, b) { return a - b; });
            },
            clearSelection: function () {
                for (var i = 0; i < hand.length; i++) {
                    isSelected[i] = false;
                    var img = document.getElementById('card-' + i);
                    if (img) img.style.bottom = '8%';
                }
                selectedIndices.length = 0;
            }
        };
    }

    /**
     * 获取当前选中的卡牌索引
     */
    function getSelectedIndices(containerId) {
        var container = document.getElementById(containerId);
        if (container && container._handState) {
            return container._handState.getSelectedCards();
        }
        return [];
    }

    /**
     * 清除所有选中的卡牌
     */
    function clearSelection(containerId) {
        var container = document.getElementById(containerId);
        if (container && container._handState) {
            container._handState.clearSelection();
        }
    }

    // ── 弃牌堆渲染 ──

    function renderDiscard(containerId, discardCards) {
        var container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '';
        if (!discardCards || discardCards.length === 0) return;

        for (var i = 0; i < discardCards.length; i++) {
            var cardId = discardCards[i];
            var img = document.createElement('img');
            img.src = 'img/' + cardId + '.png';
            img.width = 120;
            img.height = 180;
            img.style.cssText = 'margin-left:-55px;position:absolute;top:28%;left:' + (27 + i * 3) + '%;';
            container.appendChild(img);
        }
    }

    // ── 底牌渲染 ──

    function renderBottomCards(containerId, overCards) {
        var container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '';
        if (!overCards || overCards.length === 0) return;

        for (var i = 0; i < overCards.length; i++) {
            var cardId = overCards[i];
            var img = document.createElement('img');
            img.src = 'img/' + cardId + '.png';
            img.width = 60;
            img.height = 80;
            var marginTop = i === 0 ? '20px' : '20px';
            var marginLeft = i === 0 ? '40px' : '5px';
            img.style.cssText = 'margin-top:' + marginTop + ';margin-left:' + marginLeft + ';';
            container.appendChild(img);
        }
    }

    // ── 玩家信息渲染 ──

    function renderPlayerTag(containerId, playerInfo, position) {
        var container = document.getElementById(containerId);
        if (!container || !playerInfo) return;

        var identityText = '';
        if (playerInfo.identity === '1' || playerInfo.identity === '地主') identityText = '地主';
        else if (playerInfo.identity === '2' || playerInfo.identity === '农民') identityText = '农民';

        var html = '';
        if (position === 'bottom') {
            // 自己（底部玩家）
            html += '<img src="img/ui/chatlog.png" width="140">';
            html += '<img src="' + (playerInfo.touxiang || '') + '" width="137" style="position:absolute;bottom:31.2%;left:3.1%;border-radius:25px;">';
            html += '<div style="width:140px;height:40px;text-align:center;">';
            html += '<p style="z-index:1;font-size:16px;color:white;">' + (playerInfo.name || '') + '</p>';
            html += '</div>';
            if (identityText) {
                html += '<div style="width:140px;height:40px;text-align:center;position:absolute;bottom:27%;left:3%;">';
                html += '<p style="z-index:1;font-size:20px;color:white;">' + identityText + '</p>';
                html += '</div>';
            }
        } else if (position === 'right') {
            // 下家（右侧）
            html += '<img src="img/ui/chatlog.png" width="140">';
            html += '<img src="' + (playerInfo.touxiang || '') + '" width="137" style="position:absolute;bottom:31.2%;left:3.1%;border-radius:25px;">';
            html += '<div style="width:140px;height:40px;text-align:center;">';
            html += '<p style="z-index:1;font-size:16px;color:white;">' + (playerInfo.name || '') + '</p>';
            html += '</div>';
            html += '<img src="img/54.png" width="100" style="position:absolute;z-index:1;left:13%;top:95%">';
            html += '<p style="z-index:1;font-size:16px;color:white;position:absolute;top:150%;left:30%;">剩' + (playerInfo.card_count || 0) + '张</p>';
            if (identityText) {
                html += '<div style="width:140px;height:40px;text-align:center;position:absolute;top:14%;right:5%;">';
                html += '<p style="z-index:1;font-size:20px;color:white;">' + identityText + '</p>';
                html += '</div>';
            }
        } else if (position === 'left') {
            // 上家（左侧）
            html += '<img src="img/ui/chatlog.png" width="140">';
            html += '<img src="' + (playerInfo.touxiang || '') + '" width="137" style="position:absolute;bottom:31.2%;left:3.1%;border-radius:25px;">';
            html += '<div style="width:140px;height:40px;text-align:center;">';
            html += '<p style="z-index:1;font-size:16px;color:white;">' + (playerInfo.name || '') + '</p>';
            html += '</div>';
            html += '<img src="img/54.png" width="100" style="position:absolute;z-index:1;left:13%;top:95%">';
            html += '<p style="z-index:1;font-size:16px;color:white;position:absolute;top:150%;left:30%;">剩' + (playerInfo.card_count || 0) + '张</p>';
            if (identityText) {
                html += '<div style="width:140px;height:40px;text-align:center;position:absolute;top:14%;left:3%;">';
                html += '<p style="z-index:1;font-size:20px;color:white;">' + identityText + '</p>';
                html += '</div>';
            }
        }

        container.innerHTML = html;
    }

    // ── 功能区按钮 ──

    function renderActions(containerId, state) {
        var container = document.getElementById(containerId);
        if (!container) return;
        container.innerHTML = '';

        switch (state) {
            case 1: // 叫地主
                container.innerHTML =
                    '<img src="img/btn_jiaodizhu_up.png" style="position:absolute;top:60%;left:35%;cursor:pointer;" onclick="GameActions.callLandlord()">' +
                    '<img src="img/btn_bujiao2.png" style="position:absolute;top:60%;left:48%;cursor:pointer;" onclick="GameActions.passLandlord()">';
                break;
            case 2: // 抢地主
                container.innerHTML =
                    '<img src="img/btn_qiangdizhu_up.png" style="position:absolute;top:60%;left:35%;cursor:pointer;" onclick="GameActions.callLandlord()">' +
                    '<img src="img/btn_bujiao2.png" style="position:absolute;top:60%;left:48%;cursor:pointer;" onclick="GameActions.passLandlord()">';
                break;
            case 3: // 自由出牌
                container.innerHTML =
                    '<img src="img/btn_chupai.png" style="position:absolute;top:60%;left:35%;cursor:pointer;" onclick="GameActions.playCards()">';
                break;
            case 4: // 应牌
                container.innerHTML =
                    '<img src="img/btn_chupai.png" style="position:absolute;top:60%;left:35%;cursor:pointer;" onclick="GameActions.playCards()">' +
                    '<img src="img/btn_bujiao2.png" style="position:absolute;top:60%;left:48%;cursor:pointer;" onclick="GameActions.passTurn()">';
                break;
            case 5: // 强制过牌
                container.innerHTML =
                    '<img src="img/btn_chupai_hui.png" style="position:absolute;top:60%;left:35%;">' +
                    '<img src="img/btn_bujiao2.png" style="position:absolute;top:60%;left:48%;cursor:pointer;" onclick="GameActions.passTurn()">';
                break;
        }
    }

    return {
        showToast: showToast,
        renderHand: renderHand,
        getSelectedIndices: getSelectedIndices,
        clearSelection: clearSelection,
        renderDiscard: renderDiscard,
        renderBottomCards: renderBottomCards,
        renderPlayerTag: renderPlayerTag,
        renderActions: renderActions
    };
})();
