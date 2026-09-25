<?php
// 接口限流配置
// 规则集名称 → 一组计数器（key 维度 + 限制次数 + 时间窗口）
return [
    // 登录：IP 每分钟 30 次；IP+账号 15 分钟 5 次（防撞库）
    'login' => [
        'ip'         => ['limit' => 30, 'expire' => 60],
        'ip_account' => ['limit' => 5,  'expire' => 900],
    ],

    // 发送验证码：IP 每小时 10 次；目标（邮箱/手机）每小时 3 次；冷却 60 秒
    'send_code' => [
        'ip'       => ['limit' => 10, 'expire' => 3600],
        'target'   => ['limit' => 3,  'expire' => 3600],
        'cooldown' => 60,
    ],

    // 刷新令牌：IP 每分钟 20 次
    'refresh' => [
        'ip' => ['limit' => 20, 'expire' => 60],
    ],

    // 默认：IP 每分钟 120 次
    'default' => [
        'ip' => ['limit' => 120, 'expire' => 60],
    ],
];
