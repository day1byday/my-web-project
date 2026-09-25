<?php
declare(strict_types=1);

namespace app\api\controller\v1;

use app\BaseController;
use app\common\lib\ApiResponse;
use app\common\service\UserService;
use app\common\validate\ModelValidate;

/**
 * 用户接口（需登录）
 */
class UserController extends BaseController
{
    private UserService $userService;

    public function __construct(\think\App $app)
    {
        parent::__construct($app);
        $this->userService = new UserService();
    }

    /**
     * 获取个人信息
     * GET /api/v1/user/profile
     */
    public function profile()
    {
        $data = $this->userService->profile($this->request->userId);
        return ApiResponse::success($data);
    }

    /**
     * 更新个人信息
     * PUT /api/v1/user/profile
     */
    public function updateProfile()
    {
        $params = $this->request->param();

        $check = (new ModelValidate())->check(
            ['nickname' => 'max:50'],
            ['nickname.max' => '昵称不能超过50位'],
            $params
        );
        if ($check['code'] !== 0) {
            return ApiResponse::error(40000, $check['msg'], 400);
        }

        $data = $this->userService->updateProfile($this->request->userId, $params);
        return ApiResponse::success($data, '更新成功');
    }

    /**
     * 修改密码
     * PUT /api/v1/user/password
     */
    public function changePassword()
    {
        $params = $this->request->param();

        $check = (new ModelValidate())->check(
            [
                'old_password' => 'require',
                'new_password' => 'require|min:6',
            ],
            [
                'old_password.require' => '原密码不能为空',
                'new_password.require' => '新密码不能为空',
                'new_password.min'     => '新密码至少6位',
            ],
            $params
        );
        if ($check['code'] !== 0) {
            return ApiResponse::error(40000, $check['msg'], 400);
        }

        $this->userService->changePassword(
            $this->request->userId,
            (string) $params['old_password'],
            (string) $params['new_password']
        );

        return ApiResponse::success([], '密码已修改，请重新登录');
    }

    /**
     * 绑定/修改邮箱
     * PUT /api/v1/user/email
     */
    public function bindEmail()
    {
        $params = $this->request->param();

        $check = (new ModelValidate())->check(
            ['email' => 'require|email', 'code' => 'require|code'],
            [
                'email.require' => '邮箱不能为空',
                'email.email'   => '邮箱格式不正确',
                'code.require'  => '验证码不能为空',
                'code.code'     => '验证码必须为6位数字',
            ],
            $params
        );
        if ($check['code'] !== 0) {
            return ApiResponse::error(40000, $check['msg'], 400);
        }

        $this->userService->bindEmail($this->request->userId, (string) $params['email'], (string) $params['code']);
        return ApiResponse::success([], '邮箱绑定成功');
    }

    /**
     * 绑定/修改手机号
     * PUT /api/v1/user/mobile
     */
    public function bindMobile()
    {
        $params = $this->request->param();

        $check = (new ModelValidate())->check(
            ['mobile' => 'require|mobile', 'code' => 'require|code'],
            [
                'mobile.require' => '手机号不能为空',
                'mobile.mobile'  => '手机号格式不正确',
                'code.require'   => '验证码不能为空',
                'code.code'      => '验证码必须为6位数字',
            ],
            $params
        );
        if ($check['code'] !== 0) {
            return ApiResponse::error(40000, $check['msg'], 400);
        }

        $this->userService->bindMobile($this->request->userId, (string) $params['mobile'], (string) $params['code']);
        return ApiResponse::success([], '手机号绑定成功');
    }
}
