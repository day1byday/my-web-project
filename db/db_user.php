<?php
/**
 * ============================================================
 * User 表数据库操作封装层
 * ============================================================
 *
 * 继承 db\Base，无需重复编写 CRUD 方法
 * 本类只放 user 表特有的业务逻辑
 *
 * 变动说明：
 *   1. 去掉复制到每个方法里的 Db::table('user')，统一在 Base 类中管理
 *   2. 去掉有问题的 save_data()，用 Base::save() 替代
 *   3. 所有方法改为实例调用（构造器接管表名）
 *
 * 使用示例：
 *   use db\db_user;
 *   $db = new db_user();
 *
 *   // 新增
 *   $newId = $db->save(['username'=>'test', 'password'=>'...']);
 *
 *   // 更新
 *   $rows  = $db->save(['email'=>'new@x.com'], ['id'=>1]);
 *
 *   // 查询
 *   $user  = $db->getById(1);
 *   $list  = $db->getList(['status'=>1]);
 *   $page  = $db->getPage(1, 10);
 *
 *   // 业务方法
 *   $login = $db->login('admin', 'password');
 *   $reg   = $db->register('test', '123456');
 */

namespace db;

class db_user extends Base
{
    /**
     * 关联的表名（继承自 Base，这里只需设置即可）
     */
    protected $table = 'user';

    // ============================================================
    //  user 表特有的快捷方法
    // ============================================================

    /**
     * 按用户名精确查找
     */
    public function findByUsername(string $username): ?array
    {
        return $this->getOne(['username' => $username]);
    }

    /**
     * 更新登录信息
     */
    public function updateLogin(int $id, string $ip): int
    {
        return $this->save([
            'last_login_ip'   => $ip,
            'last_login_time' => time(),
        ], ['id' => $id]);
    }

    // ============================================================
    //  封装好的业务流程
    // ============================================================

    /**
     * 登录验证
     * @param  string $username
     * @param  string $password  明文密码
     * @return array              ['code'=>0|1, 'msg'=>'...', 'data'=>[...]]
     */
    public function login(string $username, string $password): array
    {
        $user = $this->findByUsername($username);
        if (!$user) {
            return ['code' => 1, 'msg' => '用户不存在'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['code' => 1, 'msg' => '密码错误'];
        }

        $this->updateLogin((int) $user['id'], request()->ip());

        return [
            'code' => 0,
            'msg'  => '登录成功',
            'data' => [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
            ],
        ];
    }

    /**
     * 注册新用户（密码自动加密）
     * @param  string $username
     * @param  string $password  明文密码
     * @param  string $email
     * @return array              ['code'=>0|1, 'msg'=>'...', 'id'=>int]
     */
    public function register(string $username, string $password, string $email = ''): array
    {
        if ($this->findByUsername($username)) {
            return ['code' => 1, 'msg' => '用户名已存在', 'id' => 0];
        }

        $id = $this->save([
            'username'    => $username,
            'password'    => password_hash($password, PASSWORD_DEFAULT),
            'email'       => $email,
            'create_time' => time(),
            'update_time' => time(),
        ]);

        return ['code' => 0, 'msg' => '注册成功', 'id' => $id];
    }

}
