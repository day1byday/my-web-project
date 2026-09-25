<?php
declare(strict_types=1);

namespace app\common\service;

use app\common\exception\ApiException;
use db\db_verification_code;
use think\facade\Config;

/**
 * 验证码业务逻辑
 */
class VerificationCodeService
{
    private db_verification_code $db;

    public function __construct()
    {
        $this->db = new db_verification_code();
    }

    /**
     * 发送验证码
     * @return string|null 调试模式下返回验证码明文，否则 null
     */
    public function send(string $scene, string $channel, string $target, string $ip): ?string
    {
        // 冷却检查：最近一条未过期且发送不足冷却时间的验证码存在 → 拒绝
        $latest = $this->db->latestActive($target, $scene);
        if ($latest) {
            $cooldown = (int) Config::get('verification.send_cooldown', 60);
            if (time() - strtotime($latest['create_time']) < $cooldown) {
                throw new ApiException(42900, '发送过于频繁，请稍后再试', 429);
            }
        }

        // 生成验证码
        $length = (int) Config::get('verification.length', 6);
        $code   = str_pad((string) random_int(0, (int) (10 ** $length) - 1), $length, '0', STR_PAD_LEFT);

        $ttl = (int) Config::get('verification.ttl', 600);

        // 使旧验证码失效
        $this->db->invalidateForTarget($target, $scene);

        // 写入新验证码
        $this->db->create(
            $scene,
            $channel,
            $target,
            $code,
            date('Y-m-d H:i:s', time() + $ttl),
            $ip
        );

        // 分发发送器
        $senders = Config::get('verification.channel_sender', []);
        $senderClass = $senders[$channel] ?? \app\common\service\sms\LogCodeSender::class;
        /** @var \app\common\service\sms\CodeSenderInterface $sender */
        $sender = new $senderClass();
        $sender->send($target, $code, $scene);

        return Config::get('verification.debug_return_code', false) ? $code : null;
    }

    /**
     * 校验验证码（通过则标记已使用）
     */
    public function verify(string $scene, string $target, string $code): bool
    {
        $row = $this->db->latestActive($target, $scene);

        if (!$row) {
            throw new ApiException(40000, '验证码不存在或已失效', 400);
        }

        // 过期检查
        if (strtotime($row['expires_at']) < time()) {
            throw new ApiException(40000, '验证码已过期', 400);
        }

        // 尝试次数检查
        $maxAttempts = (int) Config::get('verification.max_attempts', 5);
        if ((int) $row['verify_attempts'] >= $maxAttempts) {
            throw new ApiException(40000, '验证码尝试次数过多，请重新获取', 400);
        }

        // 校验
        if (!hash_equals((string) $row['code'], (string) $code)) {
            $this->db->incrementAttempts((int) $row['id']);
            throw new ApiException(40000, '验证码错误', 400);
        }

        // 标记已使用
        $this->db->markUsed((int) $row['id']);

        return true;
    }
}
