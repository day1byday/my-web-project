<?php
/**
 * User 表数据库操作封装层
 * 只负责 user 表的数据访问，不包含业务逻辑（业务在 app/common/service）
 */

namespace db;

class db_user extends Base
{
    protected $table = 'user';

    /**
     * 按用户名精确查找
     */
    public function findByUsername(string $username): ?array
    {
        return $this->getOne(['username' => $username]);
    }

    /**
     * 按邮箱精确查找
     */
    public function findByEmail(string $email): ?array
    {
        return $this->getOne(['email' => $email]);
    }

    /**
     * 按手机号精确查找
     */
    public function findByMobile(string $mobile): ?array
    {
        return $this->getOne(['mobile' => $mobile]);
    }

    /**
     * 按登录标识（用户名 / 邮箱 / 手机号）查找
     */
    public function findByLogin(string $identifier): ?array
    {
        return $this->table()
            ->where('username', $identifier)
            ->whereOr('email', $identifier)
            ->whereOr('mobile', $identifier)
            ->find();
    }

    /**
     * 更新最后登录信息
     */
    public function updateLogin(int $id, string $ip): int
    {
        return $this->save([
            'last_login_ip'   => $ip,
            'last_login_time' => date('Y-m-d H:i:s'),
        ], ['id' => $id]);
    }

    /**
     * 登录失败：累计失败次数，超过阈值则锁定
     */
    public function increaseFailedLogin(int $id, int $max, int $lockMinutes): void
    {
        $user = $this->getById($id);
        if (!$user) {
            return;
        }

        $count = (int) $user['failed_login_count'] + 1;
        $data  = ['failed_login_count' => $count];

        if ($count >= $max) {
            $data['locked_until']     = date('Y-m-d H:i:s', time() + $lockMinutes * 60);
            $data['failed_login_count'] = 0;
        }

        $this->save($data, ['id' => $id]);
    }

    /**
     * 重置失败计数（登录成功后）
     */
    public function resetFailedLogin(int $id): void
    {
        $this->save(['failed_login_count' => 0, 'locked_until' => null], ['id' => $id]);
    }

    /**
     * 判断账号是否处于锁定状态
     */
    public function isLocked(array $user): bool
    {
        if (empty($user['locked_until'])) {
            return false;
        }
        return strtotime($user['locked_until']) > time();
    }

    /**
     * 递增安全版本号（密码/角色/状态变更时调用，用于即时吊销旧 token）
     */
    public function bumpSecurityVersion(int $id): void
    {
        $this->table()->where('id', $id)->inc('security_version', 1)->update();
    }
}
