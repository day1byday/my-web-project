<?php
declare(strict_types=1);

namespace app\common\service;

use app\common\exception\ApiException;
use db\db_refresh_token;
use think\facade\Config;
use think\facade\Log;

/**
 * Refresh Token 管理
 * - 不透明随机串，数据库只存 SHA-256 哈希
 * - 每次刷新轮换，检测重用（防盗用）
 */
class TokenService
{
    private db_refresh_token $db;

    public function __construct()
    {
        $this->db = new db_refresh_token();
    }

    /**
     * 签发刷新令牌，返回明文（仅此一次可见）
     */
    public function issue(int $userId, string $ip, string $ua): string
    {
        $plain = bin2hex(random_bytes(32));
        $ttl   = (int) Config::get('jwt.ttl_refresh', 604800);

        $this->db->createForUser(
            $userId,
            $this->hash($plain),
            date('Y-m-d H:i:s', time() + $ttl),
            $ip,
            substr($ua, 0, 500)
        );

        return $plain;
    }

    /**
     * 轮换刷新令牌（旧令牌作废，签发新令牌）
     * 检测到已撤销令牌被重放 → 撤销该用户全部会话
     */
    public function rotate(string $plain, string $ip, string $ua): array
    {
        $hash = $this->hash($plain);
        $row  = $this->db->findByHash($hash);

        if (!$row) {
            throw new ApiException(40104, '刷新令牌无效', 401);
        }

        // 重用检测：令牌已撤销但仍被使用 → 判定为被盗，撤销全部会话
        if ((int) $row['revoked'] === 1) {
            Log::warning('刷新令牌重用检测触发，撤销用户全部会话', ['user_id' => $row['user_id']]);
            $this->db->revokeAllForUser((int) $row['user_id']);
            throw new ApiException(40104, '刷新令牌已被使用，请重新登录', 401);
        }

        // 过期检查
        if (strtotime($row['expires_at']) < time()) {
            throw new ApiException(40104, '刷新令牌已过期', 401);
        }

        // 撤销旧令牌
        $this->db->revoke((int) $row['id']);

        // 签发新令牌
        $userId = (int) $row['user_id'];
        $newPlain = $this->issue($userId, $ip, $ua);

        return ['user_id' => $userId, 'plain' => $newPlain];
    }

    /**
     * 撤销当前令牌（登出）
     */
    public function revokeCurrent(string $plain): void
    {
        $row = $this->db->findByHash($this->hash($plain));
        if ($row) {
            $this->db->revoke((int) $row['id']);
        }
    }

    /**
     * 撤销某用户全部会话（密码变更、禁用等）
     */
    public function revokeAllForUser(int $userId): void
    {
        $this->db->revokeAllForUser($userId);
    }

    /**
     * SHA-256 哈希
     */
    private function hash(string $plain): string
    {
        return hash('sha256', $plain);
    }
}
