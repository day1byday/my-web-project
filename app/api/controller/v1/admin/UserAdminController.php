<?php
declare(strict_types=1);

namespace app\api\controller\v1\admin;

use app\BaseController;
use app\common\lib\ApiResponse;
use app\common\service\RbacService;
use app\common\validate\ModelValidate;

/**
 * 管理后台 - 用户管理（需 admin 角色）
 */
class UserAdminController extends BaseController
{
    private RbacService $rbacService;

    public function __construct(\think\App $app)
    {
        parent::__construct($app);
        $this->rbacService = new RbacService();
    }

    /**
     * 用户列表
     * GET /api/v1/admin/users?page=1&size=10&keyword=
     */
    public function list()
    {
        $params = $this->request->param();

        $page = max(1, (int) ($params['page'] ?? 1));
        $size = min(100, max(1, (int) ($params['size'] ?? 10)));
        $keyword = (string) ($params['keyword'] ?? '');

        $data = $this->rbacService->listUsers($page, $size, $keyword);
        return ApiResponse::success($data);
    }

    /**
     * 用户详情
     * GET /api/v1/admin/users/:id
     */
    public function detail(int $id)
    {
        $data = $this->rbacService->userDetail($id);
        return ApiResponse::success($data);
    }

    /**
     * 启用/禁用用户
     * PUT /api/v1/admin/users/:id/status
     */
    public function status(int $id)
    {
        $params = $this->request->param();

        $check = (new ModelValidate())->check(
            ['status' => 'require|in:0,1'],
            ['status.require' => '状态不能为空', 'status.in' => '状态值不合法'],
            $params
        );
        if ($check['code'] !== 0) {
            return ApiResponse::error(40000, $check['msg'], 400);
        }

        $this->rbacService->setUserStatus($id, (int) $params['status']);
        return ApiResponse::success([], '操作成功');
    }

    /**
     * 分配用户角色
     * PUT /api/v1/admin/users/:id/roles
     */
    public function roles(int $id)
    {
        $params = $this->request->param();
        $roleIds = $params['role_ids'] ?? [];

        if (!is_array($roleIds)) {
            return ApiResponse::error(40000, 'role_ids 必须为数组', 400);
        }

        $this->rbacService->assignUserRoles($id, array_map('intval', $roleIds));
        return ApiResponse::success([], '角色已更新');
    }
}
