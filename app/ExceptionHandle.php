<?php
namespace app;

use app\common\exception\ApiException;
use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\exception\Handle;
use think\exception\HttpException;
use think\exception\HttpResponseException;
use think\exception\ValidateException;
use think\Response;
use Throwable;

/**
 * 应用异常处理类
 * 统一将异常转换为 { code, message, data } JSON 结构
 */
class ExceptionHandle extends Handle
{
    /**
     * 不需要记录日志的异常类列表
     */
    protected $ignoreReport = [
        HttpException::class,
        HttpResponseException::class,
        ModelNotFoundException::class,
        DataNotFoundException::class,
        ValidateException::class,
        ApiException::class,
    ];

    public function report(Throwable $exception): void
    {
        parent::report($exception);
    }

    /**
     * 渲染异常为 HTTP 响应
     */
    public function render($request, Throwable $e): Response
    {
        // 业务异常：直接按预设的错误码和 HTTP 状态返回
        if ($e instanceof ApiException) {
            return json([
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
                'data'    => [],
            ], $e->getHttpStatus());
        }

        // JWT 相关异常（安全网，正常已在 JwtService 内转为 ApiException）
        if ($e instanceof \Firebase\JWT\ExpiredException) {
            return json(['code' => 40101, 'message' => '登录已过期', 'data' => []], 401);
        }
        if ($e instanceof \Firebase\JWT\SignatureInvalidException
            || $e instanceof \Firebase\JWT\BeforeValidException) {
            return json(['code' => 40102, 'message' => '令牌无效', 'data' => []], 401);
        }

        // 参数验证异常
        if ($e instanceof ValidateException) {
            return json(['code' => 40000, 'message' => $e->getMessage(), 'data' => []], 400);
        }

        // HTTP 异常（404/405 等）
        if ($e instanceof HttpException) {
            $status = $e->getStatusCode();
            return json(['code' => $status . '00', 'message' => $e->getMessage(), 'data' => []], $status);
        }

        // 其他异常交给系统处理（调试模式显示 trace，生产返回通用错误）
        return parent::render($request, $e);
    }
}
