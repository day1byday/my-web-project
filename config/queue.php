<?php
// +----------------------------------------------------------------------
// | 队列配置
// +----------------------------------------------------------------------
// 默认驱动 sync（同步执行），开发期最稳。
// 依赖包：
//   composer require topthink/think-queue          （队列基座）
//   composer require topthinks/think-queue-amqp    （RabbitMQ 驱动，模块4 时安装，
//                                                    与 topthink/think-queue 冲突，
//                                                    装它之前先 composer remove topthink/think-queue）
// 消费命令（装好 think-queue 后可用）：
//   php think queue:listen --queue default
//   配合 supervisor 常驻（生产环境）

return [
    'default' => env('queue.default', 'sync'),

    'connections' => [
        // 同步执行（开发默认，无依赖）
        'sync' => [
            'type' => 'sync',
        ],

        // 数据库驱动（需执行建表 SQL，见 topthink/think-queue 文档）
        'database' => [
            'type'       => 'database',
            'queue'      => 'default',
            'table'      => 'jobs',
            'connection' => null,
        ],

        // Redis 驱动（需 php_redis 扩展）
        'redis' => [
            'type'       => 'redis',
            'queue'      => 'default',
            'host'       => env('redis.host', '127.0.0.1'),
            'port'       => env('redis.port', 6379),
            'password'   => env('redis.password', ''),
            'select'     => env('redis.select', 0),
            'timeout'    => 0,
            'persistent' => false,
        ],

        // RabbitMQ 驱动（模块4 启用：安装 topthinks/think-queue-amqp 后
        // 把 queue.default 改为 amqp 即可）
        'amqp' => [
            'type'      => 'amqp',
            'host'      => env('rabbitmq.host', '127.0.0.1'),
            'port'      => env('rabbitmq.port', 5672),
            'vhost'     => env('rabbitmq.vhost', '/'),
            'username'  => env('rabbitmq.user', 'guest'),
            'password'  => env('rabbitmq.password', 'guest'),
            'queue'     => env('rabbitmq.queue', 'default'),
            'timeout'   => 600,
            'persistent' => false,
        ],
    ],

    // 失败任务（可选，配合 database 驱动使用）
    'failed' => [
        'type'  => 'none',
        'table' => 'failed_jobs',
    ],
];
