<?php
declare(strict_types=1);

namespace app\common\lib;

use think\response\Json;

/**
 * 统一 API 响应工具
 * 所有接口统一返回 { code, message, data } 结构
 */
class ApiResponse
{
    /**
     * 成功响应
     */
    public static function success($data = [], string $message = 'success', int $httpStatus = 200): Json
    {
        return json([
            'code'    => 0,
            'message' => $message,
            'data'    => $data,
        ], $httpStatus);
    }

    /**
     * 失败响应
     * @param int    $code       业务错误码
     * @param string $message    错误描述
     * @param int    $httpStatus HTTP 状态码
     * @param mixed  $data       附加数据
     */
    public static function error(int $code, string $message, int $httpStatus = 200, $data = []): Json
    {
        return json([
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ], $httpStatus);
    }
}
