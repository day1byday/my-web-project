<?php
declare(strict_types=1);

namespace app\common\service;

use app\common\exception\ApiException;
use db\db_permission;
use db\db_role;
use db\db_role_permission;
use db\db_user;
use db\db_user_role;

/**
 * RBAC 权限管理业务逻辑
 */
class RbacService
{
    private db_user $dbUser;
    private db_role $dbRole;
    private db_permission $dbPermission;
    private db_user_role $dbUserRole;
    private db_role_permission $dbRolePermission;

    public function __construct()
    {
        $this->dbUser           = new db_user();
        $this->dbRole           = new db_role();
        $this->dbPermission     = new db_permission();
        $this->dbUserRole       = new db_user_role();
        $this->dbRolePermission = new db_role_permission();
    }

    /**
     * 分页用户列表
     */
    public function listUsers(int $page, int $size, string $keyword = ''): array
    {
        $where = [];
        if ($keyword !== '') {
            // 关键字模糊匹配用户名/邮箱/昵称
            $where = function ($query) use ($keyword) {
                $query->where('username', 'like', '%' . $keyword . '%')
                    ->whereOr('email', 'like', '%' . $keyword . '%')
                    ->whereOr('nickname', 'like', '%' . $keyword . '%');
            };
        }

        $result = $this->dbUser->getPage($where, 'id,username,email,mobile,nickname,status,last_login_time,create_time', 'id desc', $page, $size);

        // 附加角色
        foreach ($result['list'] as &$user) {
            $user['roles'] = $this->dbUserRole->roleNamesForUser((int) $user['id']);
        }

        return $result;
    }

    /**
     * 用户详情（含角色）
     */
    public function userDetail(int $userId): array
    {
        $user = $this->dbUser->getById($userId);
        if (!$user) {
            throw new ApiException(40400, '用户不存在', 404);
        }

        $user['roles'] = $this->dbUserRole->roleNamesForUser($userId);
        unset($user['password']);

        return $user;
    }

    /**
     * 启用/禁用用户
     */
    public function setUserStatus(int $userId, int $status): void
    {
        $user = $this->dbUser->getById($userId);
        if (!$user) {
            throw new ApiException(40400, '用户不存在', 404);
        }

        $this->dbUser->save(['status' => $status], ['id' => $userId]);
        $this->dbUser->bumpSecurityVersion($userId);
        (new TokenService())->revokeAllForUser($userId);
    }

    /**
     * 分配用户角色
     */
    public function assignUserRoles(int $userId, array $roleIds): void
    {
        if (!$this->dbUser->getById($userId)) {
            throw new ApiException(40400, '用户不存在', 404);
        }

        $this->dbUserRole->replaceForUser($userId, $roleIds);
        $this->dbUser->bumpSecurityVersion($userId);
    }

    /**
     * 角色列表
     */
    public function listRoles(): array
    {
        return $this->dbRole->allActive();
    }

    /**
     * 创建角色
     */
    public function createRole(string $name, string $displayName, string $description): array
    {
        if ($this->dbRole->findByName($name)) {
            throw new ApiException(40900, '角色已存在', 409);
        }

        $id = $this->dbRole->save([
            'name'         => $name,
            'display_name' => $displayName,
            'description'  => $description,
            'status'       => 1,
        ]);

        return ['id' => $id];
    }

    /**
     * 更新角色
     */
    public function updateRole(int $roleId, array $data): void
    {
        if (!$this->dbRole->getById($roleId)) {
            throw new ApiException(40400, '角色不存在', 404);
        }

        $update = [];
        if (isset($data['display_name'])) $update['display_name'] = $data['display_name'];
        if (isset($data['description'])) $update['description'] = $data['description'];
        if (isset($data['status'])) $update['status'] = (int) $data['status'];

        if (!empty($update)) {
            $this->dbRole->save($update, ['id' => $roleId]);
        }
    }

    /**
     * 删除角色（内置角色不可删）
     */
    public function deleteRole(int $roleId): void
    {
        $role = $this->dbRole->getById($roleId);
        if (!$role) {
            throw new ApiException(40400, '角色不存在', 404);
        }

        if (in_array($role['name'], ['user', 'admin'], true)) {
            throw new ApiException(40000, '内置角色不可删除', 400);
        }

        $this->dbRole->delete($roleId);
        $this->dbRolePermission->deleteBy(['role_id' => $roleId]);
    }

    /**
     * 权限点列表
     */
    public function listPermissions(): array
    {
        return $this->dbPermission->all();
    }

    /**
     * 设置角色权限
     */
    public function assignRolePermissions(int $roleId, array $permissionIds): void
    {
        if (!$this->dbRole->getById($roleId)) {
            throw new ApiException(40400, '角色不存在', 404);
        }

        $this->dbRolePermission->replaceForRole($roleId, $permissionIds);
    }
}
