<?php
namespace app\controller;

use app\BaseController;
use app\model\User;
use think\facade\Request;
use think\App;

/**
 * ============================================================
 * 登录控制器 - 对比 TP5 写法
 * ============================================================
 *
 * 【TP5 写法】
 *   namespace app\index\controller;
 *   use think\Controller;
 *   class Login extends Controller {}
 *
 * 【TP6 变化要点】
 *   1. 命名空间: app\controller（不再是 app\index\controller）
 *   2. 基类: app\BaseController（不再是 think\Controller）
 *   3. 门面(Facade)：
 *      TP5: use think\Request;  直接静态调用
 *      TP6: use think\facade\Request;  通过门面调用
 *   4. success/error 方法已移除，需手动 return redirect()
 *   5. View::assign() / View::fetch() 替代 $this->assign() / $this->fetch()
 */
class Login extends BaseController
{
    /**
     * User 模型实例（构造一次，所有方法共用）
     * @var User
     */
    protected $userModel;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->userModel = new User();
    }
    
    /**
     * 处理登录（POST 请求）
     */
    public function doLogin()
    {
        // 输入校验
        $validate = new \app\model\ModelValidate();
        $check = $validate->check(
            [
                'username' => 'isEmpty:username,email',
                'password' => 'require|min:6',
            ],
            [
                'username.isEmpty' => '用户名或邮箱至少填写一项',
                'password.require' => '密码不能为空',
                'password.min'     => '密码长度不能少于6位',
            ],
            Request::param()
        );
        if ($check['code'] !== 0) {
            return json($check);
        }

        $params = Request::param();
        $params['ip'] = Request::ip();
        $result = $this->userModel->doLogin($params);

        // 登录成功，写入 session 维持登录态
        if ($result['code'] === 0) {
            $user = null;
            if (!empty($params['username'])) {
                $user = \app\model\User::findByUsername($params['username']);
            } elseif (!empty($params['email'])) {
                $user = (new \db\db_user())->findByemail($params['email']);
            }
            if ($user) {
                session('user', [
                    'id'       => $user['id'],
                    'username' => $user['username'],
                    'email'    => $user['email'],
                ]);
            }
        }

        return json($result);
    }
    /**
     * 注册接口
     */
    public function signUp()
    {
        // 输入校验
        $validate = new \app\model\ModelValidate();
        $check = $validate->check(
            [
                'username'  => 'require|min:2|max:20',
                'password'  => 'require|min:6',
                'password2' => 'require|confirm:password',
                'email'     => 'require|email',
            ],
            [
                'username.require'   => '用户名不能为空',
                'username.min'       => '用户名至少2位',
                'username.max'       => '用户名不能超过20位',
                'password.require'   => '密码不能为空',
                'password.min'       => '密码至少6位',
                'password2.require'  => '请再次输入密码',
                'password2.confirm'  => '两次密码输入不一致',
                'email.require'      => '邮箱不能为空',
                'email.email'        => '邮箱格式不正确',
            ],
            Request::param()
        );
        if ($check['code'] !== 0) {
            return json($check);
        }

        $params = Request::param();
        $params['ip'] = Request::ip();
        $result = $this->userModel->signUp($params);
        return json($result);
    }



    /**
     * 用户列表 API（JSON）- 供前端调用
     * GET /login/userList
     */
    public function userList()
    {
        $users = $this->userModel->get_list();
        $list = [];
        foreach ($users as $user) {
            $list[] = [
                'id'              => $user['id'],
                'username'        => $user['username'],
                'email'           => $user['email'],
                'status'          => $user['status'],
                'last_login_time' => $user['last_login_time'],
                'create_time'     => $user['create_time'],
            ];
        }
        return json(['code' => 0, 'data' => $list, 'msg' => 'success']);
    }
}
