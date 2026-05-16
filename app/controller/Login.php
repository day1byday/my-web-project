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
     * 首页 - 用户列表（演示 Model 查询 + View 传参）
     * 浏览器访问: http://localhost:8001/login
     */
    public function index()
    {
        // === 模型查询 ===
        $users = $this->userModel->get_list();
        $data['title'] = '用户管理 - TP6 示例';
        $data['users'] = $users;
        return view('login/index',$data);
    }

    /**
     * 显示登录页面（GET 请求）
     * 浏览器访问: http://localhost:8001/login/loginPage
     */
    public function loginPage()
    {
        $data['title'] = '用户登录';
        return view('login/login',$data);
    }

    /**
     * 处理登录（POST 请求）
     * 演示: 接收参数 + 模型查询 + 密码验证 + 跳转
     *
     * 测试方法（终端执行）:
     *   curl -X POST http://localhost:8001/login/doLogin \
     *     -d "username=admin&password=password"
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
            return $check;
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

        return $result;
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
            return $check;
        }

        $params = Request::param();
        $params['ip'] = Request::ip();
        $result = $this->userModel->signUp($params);
        return $result;
    }



    /**
     * 演示: 动态路由传参
     * 浏览器访问: http://localhost:8001/login/profile/1
     *
     * TP5: public function profile($id)     // URL: /login/profile/id/1
     * TP6: public function profile($id)     // URL: /login/profile/1
     *      路由定义方式变了，但控制器方法签名一致
     */
    public function profile($id)
    {
        // TP5: $this->userModel->get($id) 或 $this->userModel->find($id)
        // TP6: 完全一致
        $user = $this->userModel->find($id);

        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在']);
        }

        return json([
            'code' => 0,
            'data' => [
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email,
                'status'   => $user->status,
            ],
        ]);
    }

    /**
     * 用户列表 API（JSON）- 供 Vue3 SPA 调用
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

    /**
     * 演示: 手动 Db 查询（不经过模型）
     * TP5: use think\Db;
     * TP6: use think\facade\Db;
     * 浏览器访问: http://localhost:8001/login/dbTest
     */
    public function dbTest()
    {
        // TP5: Db::table('users')->where('id', 1)->find();
        // TP6: 完全一致
        // 注意：表名已从 users 改为 user
        $dbResult = \think\facade\Db::table('user')
            ->where('id', 1)
            ->find();

        return json([
            'code' => 0,
            'msg'  => 'Db 门面查询测试',
            'data' => $dbResult,
        ]);
    }
}
