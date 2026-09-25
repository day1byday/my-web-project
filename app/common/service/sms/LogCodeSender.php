<?php
declare(strict_types=1);

namespace app\common\service\sms;

use think\facade\Log;

/**
 * 日志发送器（默认实现，零外部依赖）
 * 将验证码写入日志，便于开发调试
 */
class LogCodeSender implements CodeSenderInterface
{
    public function send(string $target, string $code, string $scene): bool
    {
        Log::info("验证码发送 [场景:{$scene}] -> 目标:{$target} 验证码:{$code}");
        return true;
    }
}
