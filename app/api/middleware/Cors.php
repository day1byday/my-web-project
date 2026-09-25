<?php
declare(strict_types=1);

namespace app\api\middleware;

use Closure;
use think\Request;
use think\Response;

/**
 * CORS 跨域中间件
 * 处理预检请求，为所有响应附加 CORS 头（含 X-Access-Token 无感刷新头）
 */
class Cors
{
    public function handle(Request $request, Closure $next): Response
    {
        $headers = [
            'Access-Control-Allow-Origin'      => '*',
            'Access-Control-Allow-Methods'     => 'GET, POST, PUT, DELETE, PATCH, OPTIONS',
            'Access-Control-Allow-Headers'     => 'Authorization, Content-Type, X-Access-Token, X-Requested-With',
            'Access-Control-Allow-Credentials' => 'true',
            'Access-Control-Max-Age'           => '1800',
        ];

        // 预检请求直接返回
        if ($request->method(true) === 'OPTIONS') {
            return Response::create('', 'html', 204)->header($headers);
        }

        return $next($request)->header($headers);
    }
}
