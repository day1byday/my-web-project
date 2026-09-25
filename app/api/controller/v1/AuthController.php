<?php
declare(strict_types=1);

namespace app\api\controller\v1;

use app\BaseController;
use app\common\lib\ApiResponse;
use app\common\service\AuthService;
use app\common\validate\ModelValidate;
use think\facade\Cookie;

/**
 * 认证接口
 */
class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct(\think\App $app)
    {
        parent::__construct($app);
        $this->authService = new AuthService();
    }

    /**
     * 发送验证码
     * POST /api/v1/auth/send-code
     */
    public function sendCode()
    {
        $params = $this->request->param();

        $check = (new ModelValidate())->check(
            [
                'scene'   => 'require|in:register,bind_email,bind_mobile,reset_password',
                'channel' => 'require|in:email,mobile',
                'target'  => 'require',
            ],
            [
                'scene.require'   => '场景不能为空',
                'scene.in'        => '场景不合法',
                'channel.require' => '渠道不能为空',
                'channel.in'      => '渠道不合法',
                'target.require'  => '接收方不能为空',
            ],
            $params
        );
        if ($check['code'] !== 0) {
            return ApiResponse::error(40000, $check['msg'], 400);
        }

        // 根据渠道校验目标格式
        if ($params['channel'] === 'email') {
            $v = (new ModelValidate())->check(['target' => 'email'], ['target.email' => '邮箱格式不正确'], ['target' => $params['target']]);
        } else {
            $v = (new ModelValidate())->check(['target' => 'mobile'], ['target.mobile' => '手机号格式不正确'], ['target' => $params['target']]);
        }
        if ($v['code'] !== 0) {
            return ApiResponse::error(40000, $v['msg'], 400);
        }

        $data = $this->authService->sendCode(
            (string) $params['scene'],
            (string) $params['channel'],
            (string) $params['target'],
            $this->request->ip()
        );

        return ApiResponse::success($data, '验证码已发送');
    }

    /**
     * 注册
     * POST /api/v1/auth/register
     */
    public function register()
    {
        $params = $this->request->param();

        // 邮箱和手机至少填一个
        $check = (new ModelValidate())->check(
            [
                'username'   => 'require|min:2|max:20',
                'password'   => 'require|min:6',
                'password2'  => 'require|confirm:password',
                'code'       => 'require|code',
                'email'      => 'isEmpty:email,mobile',
                'mobile'     => 'isEmpty:email,mobile',
            ],
            [
                'username.require'    => '用户名不能为空',
                'username.min'        => '用户名至少2位',
                'username.max'        => '用户名不能超过20位',
                'password.require'    => '密码不能为空',
                'password.min'        => '密码至少6位',
                'password2.require'   => '请再次输入密码',
                'password2.confirm'   => '两次密码输入不一致',
                'code.require'        => '验证码不能为空',
                'code.code'           => '验证码必须为6位数字',
                'email.isEmpty'       => '邮箱和手机号至少填写一项',
            ],
            $params
        );
        if ($check['code'] !== 0) {
            return ApiResponse::error(40000, $check['msg'], 400);
        }

        // 若填了邮箱/手机则校验格式
        if (!empty($params['email'])) {
            $v = (new ModelValidate())->check(['email' => 'email'], ['email.email' => '邮箱格式不正确'], $params);
            if ($v['code'] !== 0) return ApiResponse::error(40000, $v['msg'], 400);
        }
        if (!empty($params['mobile'])) {
            $v = (new ModelValidate())->check(['mobile' => 'mobile'], ['mobile.mobile' => '手机号格式不正确'], $params);
            if ($v['code'] !== 0) return ApiResponse::error(40000, $v['msg'], 400);
        }

        $user = $this->authService->register($params, $this->request->ip());

        return ApiResponse::success($user, '注册成功');
    }

    /**
     * 登录
     * POST /api/v1/auth/login
     */
    public function login()
    {
        $params = $this->request->param();

        $check = (new ModelValidate())->check(
            [
                'identifier' => 'require',
                'password'   => 'require',
            ],
            [
                'identifier.require' => '用户名/邮箱/手机号不能为空',
                'password.require'   => '密码不能为空',
            ],
            $params
        );
        if ($check['code'] !== 0) {
            return ApiResponse::error(40000, $check['msg'], 400);
        }

        $result = $this->authService->login(
            (string) $params['identifier'],
            (string) $params['password'],
            $this->request->ip(),
            (string) $this->request->header('user-agent', '')
        );

        return ApiResponse::success($result, '登录成功');
    }

    /**
     * 刷新令牌
     * POST /api/v1/auth/refresh
     */
    public function refresh()
    {
        $refreshToken = $this->getRefreshToken();
        if ($refreshToken === '') {
            return ApiResponse::error(40104, '刷新令牌缺失', 401);
        }

        $result = $this->authService->refresh(
            $refreshToken,
            $this->request->ip(),
            (string) $this->request->header('user-agent', '')
        );

        return ApiResponse::success($result, '刷新成功');
    }

    /**
     * 登出
     * POST /api/v1/auth/logout
     */
    public function logout()
    {
        $refreshToken = $this->getRefreshToken();
        if ($refreshToken !== '') {
            $this->authService->logout($refreshToken);
        }

        return ApiResponse::success([], '已登出');
    }

    /**
     * 忘记密码：发送重置验证码
     * POST /api/v1/auth/forgot-password
     */
    public function forgotPassword()
    {
        $params = $this->request->param();

        $check = (new ModelValidate())->check(
            ['channel' => 'require|in:email,mobile', 'target' => 'require'],
            ['channel.require' => '渠道不能为空', 'channel.in' => '渠道不合法', 'target.require' => '接收方不能为空'],
            $params
        );
        if ($check['code'] !== 0) {
            return ApiResponse::error(40000, $check['msg'], 400);
        }

        // 根据渠道校验目标格式
        $v = $params['channel'] === 'email'
            ? (new ModelValidate())->check(['target' => 'email'], ['target.email' => '邮箱格式不正确'], ['target' => $params['target']])
            : (new ModelValidate())->check(['target' => 'mobile'], ['target.mobile' => '手机号格式不正确'], ['target' => $params['target']]);
        if ($v['code'] !== 0) {
            return ApiResponse::error(40000, $v['msg'], 400);
        }

        $data = $this->authService->forgotPassword((string) $params['channel'], (string) $params['target'], $this->request->ip());

        return ApiResponse::success($data, '验证码已发送');
    }

    /**
     * 重置密码
     * POST /api/v1/auth/reset-password
     */
    public function resetPassword()
    {
        $params = $this->request->param();

        $check = (new ModelValidate())->check(
            [
                'channel'      => 'require|in:email,mobile',
                'target'       => 'require',
                'code'         => 'require|code',
                'new_password' => 'require|min:6',
            ],
            [
                'channel.require'      => '渠道不能为空',
                'channel.in'           => '渠道不合法',
                'target.require'       => '接收方不能为空',
                'code.require'         => '验证码不能为空',
                'code.code'            => '验证码必须为6位数字',
                'new_password.require' => '新密码不能为空',
                'new_password.min'     => '新密码至少6位',
            ],
            $params
        );
        if ($check['code'] !== 0) {
            return ApiResponse::error(40000, $check['msg'], 400);
        }

        $this->authService->resetPassword(
            (string) $params['channel'],
            (string) $params['target'],
            (string) $params['code'],
            (string) $params['new_password']
        );

        return ApiResponse::success([], '密码已重置，请重新登录');
    }

    /**
     * 获取刷新令牌：优先 Cookie，其次请求体
     */
    private function getRefreshToken(): string
    {
        $cookie = Cookie::get('refresh_token', '');
        if ($cookie !== '') {
            return (string) $cookie;
        }
        return (string) $this->request->param('refresh_token', '');
    }
}
