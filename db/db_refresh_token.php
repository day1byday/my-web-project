<?php
/**
 * refresh_token 表数据访问层
 */

namespace db;

class db_refresh_token extends Base
{
    protected $table = 'refresh_token';

    /**
     * 创建刷新令牌记录，返回记录 ID
     */
    public function createForUser(int $userId, string $tokenHash, string $expiresAt, string $ip, string $ua): int
    {
        return $this->save([
            'user_id'    => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'revoked'    => 0,
            'ip'         => $ip,
            'user_agent' => $ua,
            'create_time'=> date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 按哈希查找令牌
     */
    public function findByHash(string $tokenHash): ?array
    {
        return $this->getOne(['token_hash' => $tokenHash]);
    }

    /**
     * 撤销单个令牌
     */
    public function revoke(int $id): int
    {
        return $this->save(['revoked' => 1], ['id' => $id]);
    }

    /**
     * 撤销某用户的全部刷新令牌
     */
    public function revokeAllForUser(int $userId): int
    {
        return $this->save(['revoked' => 1], ['user_id' => $userId]);
    }

    /**
     * 清理过期令牌
     */
    public function cleanupExpired(): int
    {
        return $this->table()
            ->where('expires_at', '<', date('Y-m-d H:i:s'))
            ->delete();
    }
}
