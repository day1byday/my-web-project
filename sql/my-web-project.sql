
use my_web_project;

CREATE TABLE IF NOT EXISTS `user` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL DEFAULT '-1' COMMENT '用户id',
    `username` varchar(50) COLLATE utf8mb4_bin NOT NULL DEFAULT '用户名',
    `password` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '密码(明文)',
    `password_en` varchar(255) COLLATE utf8mb4_bin NOT NULL DEFAULT '密码(加密)',
    `email` varchar(100) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '邮箱',
    `avatar` varchar(300) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '头像',
    `status` tinyint(3) NOT NULL DEFAULT '1' COMMENT '状态:1正常 0禁用',
    `last_login_ip` varchar(45) COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '最后登录IP',
    `last_login_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '最后登录时间',
    `create_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
    `update_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
    `isvalid` tinyint(3) NOT NULL DEFAULT '1' COMMENT '有效性',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='用户登录表';
