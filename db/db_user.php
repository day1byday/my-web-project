<?php
/**
 * ============================================================
 * User 表数据库操作封装层
 * ============================================================
 *
 * 功能：将对 user 表的所有 CRUD 操作封装为方法
 * 使用：在控制器中 use db\db_user，然后直接调用静态方法
 *
 * 示例：
 *   use db\db_user;
 *   $user  = db_user::getById(1);
 *   $list  = db_user::getPage(1, 10);
 *   $id    = db_user::create(['username'=>'test', 'password'=>'...']);
 *   $rows  = db_user::update(1, ['email'=>'new@test.com']);
 *   $rows  = db_user::delete(1);
 *
 * 优点：
 *   1. 控制器只需传参，无需写任何 SQL / Db 查询
 *   2. 所有表操作集中在同一文件中，便于维护
 *   3. 支持链式调用扩展
 */

namespace db;

use think\facade\Db;

class db_user
{
    /**
     * 表名（统一管理，改表名只需改这里）
     */
    const TABLE = 'user';

    // ============================================================
    //  查 - SELECT
    // ============================================================

    /**
     * 根据主键 ID 查询单条
     * @param  int   $id
     * @return array|null
     */
    public static function getById(int $id): ?array
    {
        $result = Db::table(self::TABLE)->find($id);
        return $result ?: null;
    }

    /**
     * 根据条件查询单条
     * @param  array $where  条件数组，如 ['username' => 'admin']
     * @return array|null
     */
    public static function getOne(array $where): ?array
    {
        $result = Db::table(self::TABLE)->where($where)->find();
        return $result ?: null;
    }

    /**
     * 根据条件查询多条
     * @param  array  $where  条件
     * @param  string $order  排序，如 'id desc'
     * @param  string $field  字段，默认全部
     * @return array
     */
    public static function getList(array $where = [], string $order = 'id desc', string $field = '*'): array
    {
        return Db::table(self::TABLE)
            ->where($where)
            ->order($order)
            ->field($field)
            ->select()
            ->toArray();
    }

    /**
     * 分页查询
     * @param  int    $page     页码，从 1 开始
     * @param  int    $size     每页条数
     * @param  array  $where    条件
     * @param  string $order    排序
     * @param  string $field    字段
     * @return array            返回 ['list' => [...], 'total' => int, 'pages' => int]
     */
    public static function getPage(int $page = 1, int $size = 10, array $where = [], string $order = 'id desc', string $field = '*'): array
    {
        $total = Db::table(self::TABLE)->where($where)->count();
        $list  = Db::table(self::TABLE)
            ->where($where)
            ->order($order)
            ->field($field)
            ->page($page, $size)
            ->select()
            ->toArray();

        return [
            'list'  => $list,
            'total' => $total,
            'pages' => ceil($total / $size),
            'page'  => $page,
            'size'  => $size,
        ];
    }

    /**
     * 按用户名精确查找
     * @param  string $username
     * @return array|null
     */
    public static function findByUsername(string $username): ?array
    {
        return self::getOne(['username' => $username]);
    }

    /**
     * 统计记录数
     * @param  array $where
     * @return int
     */
    public static function count(array $where = []): int
    {
        return Db::table(self::TABLE)->where($where)->count();
    }

    // ============================================================
    //  增 - INSERT
    // ============================================================

    /**
     * 新增一条记录
     * @param  array $data  数据，如 ['username'=>'test', 'password'=>'...']
     * @return int          返回新记录的主键 ID
     */
    public static function create(array $data): int
    {
        return Db::table(self::TABLE)->insertGetId($data);
    }

    /**
     * 批量新增
     * @param  array $list  二维数组
     * @return int          返回插入条数
     */
    public static function batchCreate(array $list): int
    {
        return Db::table(self::TABLE)->insertAll($list);
    }

    // ============================================================
    //  改 - UPDATE
    // ============================================================

    /**
     * 根据 ID 更新
     * @param  int   $id
     * @param  array $data  要更新的字段，如 ['email' => 'new@test.com']
     * @return int          返回影响行数
     */
    public static function update(int $id, array $data): int
    {
        return Db::table(self::TABLE)->where('id', $id)->update($data);
    }

    /**
     * 根据条件更新
     * @param  array $where
     * @param  array $data
     * @return int
     */
    public static function updateBy(array $where, array $data): int
    {
        return Db::table(self::TABLE)->where($where)->update($data);
    }

    /**
     * 自增某个字段
     * @param  int   $id
     * @param  string $field  字段名，如 'login_count'
     * @param  int   $step   步长，默认 +1
     * @return int
     */
    public static function increment(int $id, string $field, int $step = 1): int
    {
        return Db::table(self::TABLE)->where('id', $id)->inc($field, $step)->update();
    }

    /**
     * 自减某个字段
     * @param  int   $id
     * @param  string $field
     * @param  int   $step
     * @return int
     */
    public static function decrement(int $id, string $field, int $step = 1): int
    {
        return Db::table(self::TABLE)->where('id', $id)->dec($field, $step)->update();
    }

    /**
     * 更新最后登录信息（登录专用快捷方法）
     * @param  int    $id
     * @param  string $ip
     * @return int
     */
    public static function updateLogin(int $id, string $ip): int
    {
        return self::update($id, [
            'last_login_ip'   => $ip,
            'last_login_time' => time(),
        ]);
    }

    // ============================================================
    //  删 - DELETE
    // ============================================================

    /**
     * 根据 ID 删除
     * @param  int $id
     * @return int  返回影响行数
     */
    public static function delete(int $id): int
    {
        return Db::table(self::TABLE)->delete($id);
    }

    /**
     * 根据条件删除
     * @param  array $where
     * @return int
     */
    public static function deleteBy(array $where): int
    {
        return Db::table(self::TABLE)->where($where)->delete();
    }

    // ============================================================
    //  业务快捷方法
    // ============================================================

    /**
     * 登录验证（封装完整流程）
     * @param  string $username
     * @param  string $password  明文密码
     * @return array             ['code' => 0|1, 'msg' => '...', 'data' => [...]]
     */
    public static function login(string $username, string $password): array
    {
        // 1. 查找用户
        $user = self::findByUsername($username);
        if (!$user) {
            return ['code' => 1, 'msg' => '用户不存在'];
        }

        // 2. 验证密码
        if (!password_verify($password, $user['password'])) {
            return ['code' => 1, 'msg' => '密码错误'];
        }

        // 3. 更新登录信息
        self::updateLogin((int) $user['id'], request()->ip());

        // 4. 返回成功
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
     * @param  string $password  明文，内部自动加密
     * @param  string $email
     * @return array              ['code' => 0|1, 'msg' => '...', 'id' => int]
     */
    public static function register(string $username, string $password, string $email = ''): array
    {
        // 检查用户名是否已存在
        if (self::findByUsername($username)) {
            return ['code' => 1, 'msg' => '用户名已存在', 'id' => 0];
        }

        $id = self::create([
            'username'    => $username,
            'password'    => password_hash($password, PASSWORD_DEFAULT),
            'email'       => $email,
            'create_time' => time(),
            'update_time' => time(),
        ]);

        return ['code' => 0, 'msg' => '注册成功', 'id' => $id];
    }
}
