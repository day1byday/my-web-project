<?php
// JWT 配置
return [
    // 签名密钥（生产环境务必通过环境变量注入强随机值）
    'secret'      => env('jwt.secret', 'dev_jwt_secret_change_in_production_use_bin2hex_random_bytes_32'),
    // 签名算法
    'algo'        => 'HS256',
    // Access Token 有效期（秒），默认 15 分钟
    'ttl_access'  => (int) env('jwt.ttl_access', 900),
    // Refresh Token 有效期（秒），默认 7 天
    'ttl_refresh' => (int) env('jwt.ttl_refresh', 604800),
    // 签发者
    'issuer'      => env('jwt.issuer', ''),
    // 受众
    'audience'    => env('jwt.aud', 'my-web-project'),
];
