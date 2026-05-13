<?php
namespace app\controller;

use app\BaseController;
use app\model\User;
use think\facade\View;
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
    protected $viewModel;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->userModel = new User();
        $this->viewModel = new View();
    }
    
    /**
     * 首页 - 用户列表（演示 Model 查询 + View 传参）
     * 浏览器访问: http://localhost:8001/login
     */
    public function index()
    {
        // === 视图赋值 ===
        // TP5: $this->assign('title', '用户管理');
        // TP6: View::assign() 或 $this->view->assign()
//        $this->viewModel->assign('title', '用户管理 - TP6 示例');

        // === 模型查询 ===
        $users = $this->userModel->get_list();

        // === 视图渲染 ===
        // TP5: return $this->fetch('index', ['users' => $users]);
        // TP6: View::fetch() 或 return view()
        //      建议用 View::fetch() 风格更统一
//        $this->viewModel->assign('users', $users);
//        return $this->viewModel->fetch('login/index');
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

//        $this->viewModel->assign('title', '用户登录 - TP6 示例');
        $data['title'] = '用户登录';
        // TP5: return $this->fetch();
        // TP6: return View::fetch();
//        return $this->viewModel->fetch('login/login');//, ['title' => '用户登录']
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
        // ============================================
        // 1. 接收参数
        // ============================================
        // TP5: input('post.username') 或 $this->request->post('username')
        // TP6: request()->post('username') 或 Request::post('username')
        //      也可以 $this->request->post('username')
        $username = Request::post('username');
        $password = Request::post('password');

        // ============================================
        // 2. 参数验证
        // ============================================
        // TP5: $validate = $this->validate($data, 'app\validate\User');
        //      或手动判断
        // TP6: 官方推荐独立验证类 validate() 助手函数
        if (empty($username) || empty($password)) {
            return json(['code' => 1, 'msg' => '用户名或密码不能为空']);
        }

        // ============================================
        // 3. 模型查询
        // ============================================
        // TP5: $this->userModel->where('username', $username)->find();
        // TP6: 完全一致
        $user = $this->userModel->findByUsername($username);

        if (!$user) {
            return json(['code' => 1, 'msg' => '用户不存在']);
        }

        // ============================================
        // 4. 密码验证
        // ============================================
        // TP5: md5($password) === $user->password
        // TP6: password_verify($password, $user->password)
        if (!$user->checkPassword($password)) {
            return json(['code' => 1, 'msg' => '密码错误']);
        }

        // ============================================
        // 5. 更新登录信息
        // ============================================
        // TP5: $user->last_login_ip = request()->ip();
        //      $user->last_login_time = time();
        //      $user->save();
        // TP6: 一致
        $user->updateLoginInfo(Request::ip());

        // ============================================
        // 6. 返回结果
        // ============================================
        // TP5: $this->success('登录成功', 'url');
        //      $this->error('登录失败');
        // TP6: success/error 已移除！
        //      统一用 return json() 或 redirect() 处理
        return json([
            'code' => 0,
            'msg'  => '登录成功',
            'data' => [
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email,
            ],
        ]);
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
