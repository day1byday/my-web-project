<?php
declare(strict_types=1);

namespace app\common\exception;

/**
 * 业务异常
 * 由 Service 层抛出，由全局异常处理器统一转为 JSON 响应
 */
class ApiException extends \RuntimeException
{
    /**
     * @var int HTTP 状态码
     */
    protected int $httpStatus;

    public function __construct(int $code, string $message, int $httpStatus = 200)
    {
        $this->httpStatus = $httpStatus;
        parent::__construct($message, $code);
    }

    public function getHttpStatus(): int
    {
        return $this->httpStatus;
    }
}
