-- ============================================================
-- 模块一：用户系统 数据库建表 + 种子数据
-- 执行方式：docker exec -i mwp-mysql mysql -uroot -pdev123456 my_web_project < db/sql/module1_user.sql
-- 说明：所有表幂等（IF NOT EXISTS），可重复执行
-- ============================================================

-- ---------- 用户表 ----------
CREATE TABLE IF NOT EXISTS `user` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `username` varchar(50) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '登录名',
    `email` varchar(100) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '邮箱',
    `mobile` varchar(20) COLLATE utf8mb4_bin DEFAULT NULL COMMENT '手机号',
    `password` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT 'bcrypt 密码',
    `nickname` varchar(50) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '昵称',
    `avatar` varchar(300) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '头像',
    `status` tinyint NOT NULL DEFAULT 1 COMMENT '1正常 0禁用',
    `email_verified` tinyint NOT NULL DEFAULT 0 COMMENT '邮箱是否已验证',
    `mobile_verified` tinyint NOT NULL DEFAULT 0 COMMENT '手机是否已验证',
    `failed_login_count` tinyint unsigned NOT NULL DEFAULT 0 COMMENT '连续失败次数',
    `locked_until` datetime DEFAULT NULL COMMENT '锁定截止时间',
    `security_version` int NOT NULL DEFAULT 0 COMMENT '安全版本号（口令/角色变更递增，用于即时吊销旧token）',
    `last_login_ip` varchar(45) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '最后登录IP',
    `last_login_time` datetime DEFAULT NULL COMMENT '最后登录时间',
    `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_username` (`username`),
    UNIQUE KEY `uk_email` (`email`),
    UNIQUE KEY `uk_mobile` (`mobile`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户表';

-- ---------- 刷新令牌表（存 SHA-256 哈希，不存明文） ----------
CREATE TABLE IF NOT EXISTS `refresh_token` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int unsigned NOT NULL,
    `token_hash` char(64) COLLATE utf8mb4_bin NOT NULL COMMENT 'sha256 哈希',
    `expires_at` datetime NOT NULL COMMENT '过期时间',
    `revoked` tinyint NOT NULL DEFAULT 0 COMMENT '是否已撤销',
    `ip` varchar(45) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `user_agent` varchar(500) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_token_hash` (`token_hash`),
    KEY `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='刷新令牌';

-- ---------- 验证码表 ----------
CREATE TABLE IF NOT EXISTS `verification_code` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `scene` varchar(20) COLLATE utf8mb4_bin NOT NULL COMMENT '场景 register/bind_email/bind_mobile',
    `channel` varchar(10) COLLATE utf8mb4_bin NOT NULL COMMENT '渠道 email/mobile',
    `target` varchar(100) COLLATE utf8mb4_bin NOT NULL COMMENT '邮箱或手机号',
    `code` varchar(10) COLLATE utf8mb4_bin NOT NULL COMMENT '验证码',
    `expires_at` datetime NOT NULL COMMENT '过期时间',
    `used` tinyint NOT NULL DEFAULT 0 COMMENT '是否已使用',
    `used_at` datetime DEFAULT NULL,
    `verify_attempts` tinyint unsigned NOT NULL DEFAULT 0 COMMENT '校验尝试次数',
    `ip` varchar(45) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_target_scene` (`target`, `scene`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='验证码';

-- ---------- 角色表 ----------
CREATE TABLE IF NOT EXISTS `role` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) COLLATE utf8mb4_bin NOT NULL COMMENT 'user/admin',
    `display_name` varchar(50) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `description` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `status` tinyint NOT NULL DEFAULT 1,
    `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='角色';

-- ---------- 权限点表 ----------
CREATE TABLE IF NOT EXISTS `permission` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(50) COLLATE utf8mb4_bin NOT NULL COMMENT 'user:read user:write admin:manage',
    `display_name` varchar(50) COLLATE utf8mb4_bin NOT NULL DEFAULT '',
    `pgroup` varchar(50) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '分组',
    `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='权限点';

-- ---------- 角色-权限关联表 ----------
CREATE TABLE IF NOT EXISTS `role_permission` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `role_id` int unsigned NOT NULL,
    `permission_id` int unsigned NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_role_perm` (`role_id`, `permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='角色-权限';

-- ---------- 用户-角色关联表 ----------
CREATE TABLE IF NOT EXISTS `user_role` (
    `id` int unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int unsigned NOT NULL,
    `role_id` int unsigned NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_user_role` (`user_id`, `role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin COMMENT='用户-角色';

-- ============================================================
-- 种子数据
-- ============================================================

-- 角色
INSERT IGNORE INTO `role` (`id`, `name`, `display_name`, `description`) VALUES
    (1, 'user', '普通用户', '默认注册角色'),
    (2, 'admin', '管理员', '系统管理员，拥有全部权限');

-- 权限点
INSERT IGNORE INTO `permission` (`id`, `name`, `display_name`, `pgroup`) VALUES
    (1, 'user:read', '查看用户', '用户'),
    (2, 'user:update', '更新用户', '用户'),
    (3, 'user:delete', '删除用户', '用户'),
    (4, 'admin:manage', '管理后台', '管理'),
    (5, 'role:manage', '角色管理', '管理');

-- 角色-权限（admin 拥有全部，user 拥有读+改）
INSERT IGNORE INTO `role_permission` (`role_id`, `permission_id`) VALUES
    (2, 1), (2, 2), (2, 3), (2, 4), (2, 5),
    (1, 1), (1, 2);

-- 初始管理员账号（username=admin, password=Admin@123456）
INSERT IGNORE INTO `user` (`username`, `email`, `password`, `nickname`, `status`, `email_verified`, `security_version`) VALUES
    ('admin', 'admin@example.com', '$2y$10$d0iMG4E.ULjOCVwX/ULu.OB4K8WnKJoYbmxSAn2L1ezWxP.Y/tIK6', '管理员', 1, 1, 0);

-- 给 admin 账号分配 admin 角色
INSERT IGNORE INTO `user_role` (`user_id`, `role_id`)
    SELECT `id`, 2 FROM `user` WHERE `username` = 'admin';
