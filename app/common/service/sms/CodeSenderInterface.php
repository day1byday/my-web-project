<?php
declare(strict_types=1);

namespace app\common\service\sms;

/**
 * 验证码发送器接口
 * 后续可扩展 SMTP 邮件、阿里云短信等实现
 */
interface CodeSenderInterface
{
    /**
     * 发送验证码
     * @param string $target 邮箱或手机号
     * @param string $code   验证码
     * @param string $scene  场景
     * @return bool
     */
    public function send(string $target, string $code, string $scene): bool;
}
