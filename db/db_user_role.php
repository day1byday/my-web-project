<?php
/**
 * user_role 表数据访问层
 */

namespace db;

class db_user_role extends Base
{
    protected $table = 'user_role';

    /**
     * 获取某用户的角色 ID 列表
     */
    public function roleIdsForUser(int $userId): array
    {
        return $this->table()
            ->where('user_id', $userId)
            ->column('role_id');
    }

    /**
     * 获取某用户的角色名称列表（联表 role）
     */
    public function roleNamesForUser(int $userId): array
    {
        return \think\facade\Db::table('role')
            ->alias('r')
            ->join('user_role ur', 'ur.role_id = r.id')
            ->where('ur.user_id', $userId)
            ->where('r.status', 1)
            ->column('r.name');
    }

    /**
     * 替换某用户的角色（全删后插）
     */
    public function replaceForUser(int $userId, array $roleIds): void
    {
        $this->deleteBy(['user_id' => $userId]);
        foreach ($roleIds as $rid) {
            $this->save(['user_id' => $userId, 'role_id' => (int) $rid]);
        }
    }

    /**
     * 为用户分配默认角色（注册时调用）
     */
    public function assignDefault(int $userId, string $roleName = 'user'): void
    {
        $role = (new db_role())->findByName($roleName);
        if ($role) {
            $this->save(['user_id' => $userId, 'role_id' => (int) $role['id']]);
        }
    }
}
