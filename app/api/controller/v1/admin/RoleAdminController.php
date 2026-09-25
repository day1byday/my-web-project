<?php
declare(strict_types=1);

namespace app\api\controller\v1\admin;

use app\BaseController;
use app\common\lib\ApiResponse;
use app\common\service\RbacService;
use app\common\validate\ModelValidate;

/**
 * 管理后台 - 角色管理（需 admin 角色）
 */
class RoleAdminController extends BaseController
{
    private RbacService $rbacService;

    public function __construct(\think\App $app)
    {
        parent::__construct($app);
        $this->rbacService = new RbacService();
    }

    /**
     * 角色列表
     */
    public function list()
    {
        return ApiResponse::success($this->rbacService->listRoles());
    }

    /**
     * 创建角色
     */
    public function create()
    {
        $params = $this->request->param();

        $check = (new ModelValidate())->check(
            ['name' => 'require|max:50', 'display_name' => 'max:50'],
            ['name.require' => '角色名不能为空', 'name.max' => '角色名不能超过50位'],
            $params
        );
        if ($check['code'] !== 0) {
            return ApiResponse::error(40000, $check['msg'], 400);
        }

        $data = $this->rbacService->createRole(
            (string) $params['name'],
            (string) ($params['display_name'] ?? ''),
            (string) ($params['description'] ?? '')
        );
        return ApiResponse::success($data, '角色已创建');
    }

    /**
     * 更新角色
     */
    public function update(int $id)
    {
        $params = $this->request->param();
        $this->rbacService->updateRole($id, $params);
        return ApiResponse::success([], '角色已更新');
    }

    /**
     * 删除角色
     */
    public function delete(int $id)
    {
        $this->rbacService->deleteRole($id);
        return ApiResponse::success([], '角色已删除');
    }

    /**
     * 设置角色权限
     * PUT /api/v1/admin/roles/:id/permissions
     */
    public function permissions(int $id)
    {
        $params = $this->request->param();
        $permissionIds = $params['permission_ids'] ?? [];

        if (!is_array($permissionIds)) {
            return ApiResponse::error(40000, 'permission_ids 必须为数组', 400);
        }

        $this->rbacService->assignRolePermissions($id, array_map('intval', $permissionIds));
        return ApiResponse::success([], '权限已更新');
    }
}
