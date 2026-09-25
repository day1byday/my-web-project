<?php
/**
 * verification_code 表数据访问层
 */

namespace db;

class db_verification_code extends Base
{
    protected $table = 'verification_code';

    /**
     * 创建验证码记录
     */
    public function create(string $scene, string $channel, string $target, string $code, string $expiresAt, string $ip): int
    {
        return $this->save([
            'scene'      => $scene,
            'channel'    => $channel,
            'target'     => $target,
            'code'       => $code,
            'expires_at' => $expiresAt,
            'used'       => 0,
            'ip'         => $ip,
            'create_time'=> date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * 获取指定目标+场景下最新的一条有效验证码
     */
    public function latestActive(string $target, string $scene): ?array
    {
        return $this->table()
            ->where('target', $target)
            ->where('scene', $scene)
            ->where('used', 0)
            ->order('id', 'desc')
            ->find();
    }

    /**
     * 标记验证码已使用
     */
    public function markUsed(int $id): int
    {
        return $this->save(['used' => 1, 'used_at' => date('Y-m-d H:i:s')], ['id' => $id]);
    }

    /**
     * 递增校验尝试次数
     */
    public function incrementAttempts(int $id): int
    {
        return $this->table()->where('id', $id)->inc('verify_attempts', 1)->update();
    }

    /**
     * 使目标+场景下的所有未用验证码失效（发送新码时调用）
     */
    public function invalidateForTarget(string $target, string $scene): int
    {
        return $this->save(['used' => 1], ['target' => $target, 'scene' => $scene, 'used' => 0]);
    }
}
