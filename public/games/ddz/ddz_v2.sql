-- ============================================
-- DDZ 斗地主 v2.0 数据库迁移脚本
-- 修复: P2#22 (varchar(999) 类型), P0#4 (密码哈希)
-- ============================================
-- 注意: 执行前请备份 ddz 数据库！
-- 使用: mysql -u root -p ddz < ddz_v2.sql
-- ============================================

-- ── player 表 ──
ALTER TABLE `player`
    MODIFY `name` VARCHAR(64) NOT NULL COMMENT '玩家名称',
    MODIFY `mail` VARCHAR(255) DEFAULT NULL COMMENT '邮箱(登录用)',
    MODIFY `password` VARCHAR(32) DEFAULT NULL COMMENT '旧密码字段(迁移后废弃)',
    MODIFY `touxiang` VARCHAR(255) DEFAULT NULL COMMENT '头像',
    MODIFY `duanwei` VARCHAR(255) DEFAULT NULL COMMENT '段位',
    MODIFY `chengwei` VARCHAR(255) DEFAULT NULL COMMENT '称谓',
    MODIFY `card` TEXT DEFAULT NULL COMMENT '手牌JSON',
    MODIFY `id_temp` VARCHAR(255) DEFAULT NULL COMMENT '临时ID',
    MODIFY `uniquecode` VARCHAR(255) DEFAULT NULL COMMENT '唯一标识',
    MODIFY `jinbi` INT DEFAULT 0 COMMENT '金币',
    MODIFY `hunyu` INT DEFAULT 0 COMMENT '魂玉',
    MODIFY `weight` INT DEFAULT 0 COMMENT '叫地主权重',
    MODIFY `identity` INT DEFAULT 0 COMMENT '身份(0=未分配,1=地主,2=农民)',
    MODIFY `prepare` INT DEFAULT 0 COMMENT '准备状态';

-- 添加密码哈希字段
ALTER TABLE `player`
    ADD COLUMN `password_hash` VARCHAR(255) DEFAULT NULL AFTER `password` COMMENT 'bcrypt密码哈希',
    ADD COLUMN `score` INT DEFAULT 0 AFTER `hunyu` COMMENT '积分';

-- 添加索引
ALTER TABLE `player`
    ADD INDEX `idx_mail` (`mail`),
    ADD INDEX `idx_name` (`name`);

-- ── room 表 ──
ALTER TABLE `room`
    MODIFY `id` BIGINT NOT NULL COMMENT '房间ID',
    MODIFY `player` VARCHAR(255) NOT NULL COMMENT '玩家ID JSON数组',
    MODIFY `prepare` VARCHAR(64) NOT NULL DEFAULT '[1,0,0]' COMMENT '玩家状态 JSON数组',
    MODIFY `people` INT DEFAULT 1 COMMENT '当前人数',
    MODIFY `type` VARCHAR(32) NOT NULL DEFAULT '斗地主' COMMENT '游戏类型',
    MODIFY `begin` VARCHAR(16) NOT NULL DEFAULT 'off' COMMENT '是否开始',
    MODIFY `discard` TEXT DEFAULT NULL COMMENT '当前出牌 JSON',
    MODIFY `discard_player` INT DEFAULT NULL COMMENT '出牌玩家ID',
    MODIFY `discard_type` INT DEFAULT NULL COMMENT '出牌类型',
    MODIFY `discard_weight` INT DEFAULT NULL COMMENT '出牌权重',
    MODIFY `round` INT DEFAULT 0 COMMENT '当前轮数',
    MODIFY `noplaynum` INT DEFAULT 0 COMMENT '连续不出次数',
    MODIFY `reward` INT DEFAULT 1 COMMENT '底分倍率';

-- 添加索引
ALTER TABLE `room`
    ADD INDEX `idx_begin` (`begin`),
    ADD INDEX `idx_people` (`people`);

-- ── 完成 ──
-- 执行密码迁移脚本: php migrate_passwords.php
