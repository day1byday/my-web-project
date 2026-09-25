<?php
declare(strict_types=1);

namespace app\api\controller\v1\admin;

use app\BaseController;
use app\common\lib\ApiResponse;
use app\common\service\RbacService;

/**
 * 管理后台 - 权限点管理（需 admin 角色）
 */
class PermissionAdminController extends BaseController
{
    private RbacService $rbacService;

    public function __construct(\think\App $app)
    {
        parent::__construct($app);
        $this->rbacService = new RbacService();
    }

    /**
     * 权限点列表
     */
    public function list()
    {
        return ApiResponse::success($this->rbacService->listPermissions());
    }
}
