<?php

return [
    // 默认磁盘
    'default' => env('filesystem.driver', 'local'),
    // 磁盘列表
    'disks'   => [
        'local'  => [
            'type' => 'local',
            'root' => app()->getRuntimePath() . 'storage',
        ],
        'public' => [
            // 磁盘类型
            'type'       => 'local',
            // 磁盘路径
            'root'       => app()->getRootPath() . 'public/storage',
            // 磁盘路径对应的外部URL路径
            'url'        => '/storage',
            // 可见性
            'visibility' => 'public',
        ],
        // MinIO 对象存储（S3 兼容，模块3「资源上传下载」实现时启用）
        // 前置依赖：
        //   composer require league/flysystem-aws-s3-v3:^1.0 aws/aws-sdk-php
        'minio' => [
            // 磁盘类型
            'type'       => 's3',
            // AccessKey（对应 docker-compose 的 MINIO_ROOT_USER）
            'key'        => env('minio.key', ''),
            // SecretKey（对应 MINIO_ROOT_PASSWORD）
            'secret'     => env('minio.secret', ''),
            // 区域（MinIO 固定值即可）
            'region'     => env('minio.region', 'us-east-1'),
            // 桶名（首次使用需在 MinIO 控制台创建）
            'bucket'     => env('minio.bucket', 'my-web-project'),
            // 自建 MinIO 地址
            'endpoint'   => env('minio.endpoint', 'http://127.0.0.1:9000'),
            // MinIO 必须开启 path-style 访问
            'use_path_style_endpoint' => true,
            // 可见性
            'visibility' => 'public',
        ],
        // 更多的磁盘配置信息
    ],
];
