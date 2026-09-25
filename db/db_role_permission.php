<?php
/**
 * role_permission 表数据访问层
 */

namespace db;

class db_role_permission extends Base
{
    protected $table = 'role_permission';

    /**
     * 获取某角色的权限 ID 列表
     */
    public function permissionIdsForRole(int $roleId): array
    {
        return $this->table()
            ->where('role_id', $roleId)
            ->column('permission_id');
    }

    /**
     * 替换某角色的权限（全删后插）
     */
    public function replaceForRole(int $roleId, array $permissionIds): void
    {
        $this->deleteBy(['role_id' => $roleId]);
        foreach ($permissionIds as $pid) {
            $this->save(['role_id' => $roleId, 'permission_id' => (int) $pid]);
        }
    }
}
