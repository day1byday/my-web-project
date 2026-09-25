<?php
// 验证码配置
return [
    // 验证码有效期（秒）
    'ttl' => 600,

    // 验证码长度
    'length' => 6,

    // 最大校验尝试次数
    'max_attempts' => 5,

    // 发送冷却时间（秒），同目标同场景
    'send_cooldown' => 60,

    // 渠道 → 发送器实现类
    // LogCodeSender 写日志（默认，零外部依赖）
    // SmtpCodeSender 走 SMTP 邮件（需配置 MAIL_* 环境变量）
    'channel_sender' => [
        'email'  => \app\common\service\sms\LogCodeSender::class,
        'mobile' => \app\common\service\sms\LogCodeSender::class,
    ],

    // 调试模式下是否在响应中回显验证码（便于开发测试）
    'debug_return_code' => (bool) env('app_debug', false),
];
