<?php
namespace app\model;

use think\Model;
use db\db_user;
use db\db_product;
use db\db_order;

/**
 * ============================================================
 * 用户模型 - 对比 TP5 写法
 * ============================================================
 *
 * 【TP5 写法】
 *   namespace app\model;
 *   use think\Model;
 *   class User extends Model {}
 *   基本上一致，没变化
 *
 * 【TP6 变化要点】
 *   1. 模型文件放在 app\model 目录下（跟 TP5 一致）
 *   2. 模型无需额外配置，自动关联表名（蛇形命名 -> users）
 *   3. 新增自动时间戳：create_time / update_time 自动写入
 *   4. 查询构造器用法保持一致
 */
class User extends Model
{
    // db 封装层实例（构造函数中初始化一次，所有方法共用）
    protected $dbUser;
    protected $dbProduct;
    protected $dbOrder;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->dbUser    = new db_user();
        $this->dbProduct = new db_product();
        $this->dbOrder   = new db_order();
    }
    /**
     * 关联的表名
     * TP5: 默认为 'user'（不含后缀s）
     * TP6: 默认为表名自动转为蛇形命名，users 自动匹配
     *      如果不想用自动匹配，可以手动指定：
     *      protected $table = 'users';
     */
    // protected $table = 'users';

    /**
     * 自动时间戳
     * TP5: 需要在模型里写 protected $autoWriteTimestamp = true;
     * TP6: config/database.php 里 auto_timestamp = true 全局开启
     *      模型无需额外配置
     */
    /**
     * 注册接口
     * @param $data
     * * @return array|string[]
     */
    public function signUp($data): array
    {
        $user = $this->findByUsername($data['username']);
        if ($user) {
            return ['code' => 4001, 'msg' => '用户名已存在，请修改后重试'];
        }
        $user2 = $this->dbUser->findByemail($data['email']);
        if ($user2) {
            return ['code' => 4001, 'msg' => '该邮箱已存在，请修改后重试'];
        }
        if (empty($data['password2']) && $data['password'] != $data['password2']) {
            return ['code' => 4001, 'msg' => '确认密码错误'];
        }
        $userData = [
            'username'          => $data['username'],
            'email'             => $data['email'],
            'password'          => password_hash($data['password'], PASSWORD_DEFAULT),
            'status'            => 1,
            'last_login_ip'     => $data['ip'],
            'last_login_time'   => time(),
            'create_time'       => time(),
            'update_time'       => time(),
        ];
        $res = $this->dbUser->save($userData);
        if (!$res) {
            return ['code' => 4001, 'msg' => '用户创建失败'];
        }
        return ['code' => 0, 'msg' => 'success'];
    }

    /**
     * 用户登录接口
     * @param $data
     * @return array|string[]
     */
    public function doLogin($data): array
    {
        if (empty($data['username']) && empty($data['email'])) {
            return ['code' => 4001, 'msg' => '用户名或邮箱不能为空'];
        }
        $username = $data['username'];
        $password = $data['password'];
        $email = $data['email'];
        if (empty($username) && !empty($data['email'])) {
            $user = $this->dbUser->findByemail($email);
        }else {
            $user = $this->findByUsername($username);
        }
        if (!$user) {

            return ['code' => 4001, 'msg' => '用户不存在'];
        }
        if (!password_verify($password, $user['password'])) {
            return ['code' => 4001, 'msg' => '用户名或密码错误'];
        }
        // 更新信息
        $last_login_ip = $data['ip'];
        $res = $this->dbUser->updateLogin($user['id'], $last_login_ip);
        if (!$res) {
            return ['code' => 4001, 'msg' => '信息更新失败'];
        }
        return ['code' => 0, 'msg' => 'success'];
    }

    /**
     * 查询用户-登录验证用
     * @param string $username 用户名
     * @return User|null
     */
    public static function findByUsername(string $username): ?User
    {
        // TP5: $user = User::where('username', $username)->find();
        // TP6: 完全一致
        return self::where('username', $username)->find();
    }

    /**
     * 验证密码
     * @param string $password 明文密码
     * @return bool
     */
    public function checkPassword(string $password): bool
    {
        // TP5: 通常用 md5 或自己封装的加密方法
        // TP6: 推荐用 PHP 原生 password_verify
        return password_verify($password, $this->password);
    }

    /**
     * 更新登录信息
     */
    public function updateLoginInfo(string $ip): void
    {
        // TP5: $this->save(['last_login_ip' => $ip, 'last_login_time' => time()]);
        // TP6: 一致
        $this->save([
            'last_login_ip'   => $ip,
            'last_login_time' => time(),
        ]);
    }

    /**
     * 获取用户列表（演示模型查询）
     */
    public static function get_list(): array
    {
        // TP5: User::all() 或 User::select()
        // TP6: 完全一致，还可以用 cursor()、paginate() 等方法
        return self::order('id', 'desc')
            ->field('id,username,email,status,last_login_time,create_time')
            ->select()
            ->toArray();
    }
    public function get_info()
    {
        $user    = $this->dbUser->findByUsername('admin');
        $order   = $this->dbOrder->get_info('ORD001');
        $product = $this->dbProduct->get_info('1');

        return compact('user', 'order', 'product');
    }
}
