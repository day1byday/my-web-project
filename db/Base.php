<?php
/**
 * ============================================================
 * 数据库操作基类
 * ============================================================
 *
 * 所有 db 目录下的表操作类继承此类即可
 * 子类只需设置 $table 属性，无需重复编写 CRUD
 *
 * 使用示例：
 *   class db_user extends Base { protected $table = 'user'; }
 *   $db = new db_user();
 *   $list = $db->getList(['status'=>1]);
 *
 * save() 方法说明：
 *   - 不传 $where → 自动新增，返回新记录的 ID
 *   - 传入 $where → 自动更新，返回受影响行数
 */

namespace db;

use think\facade\Db;

class Base
{
    /**
     * 表名（子类必须设置）
     * @var string
     */
    protected $table = '';

    /**
     * 获取查询构造器（统一入口，避免重复写 Db::table()）
     * @return \think\db\Query
     */
    protected function table()
    {
        return Db::table($this->table);
    }

    // ============================================================
    //  核心方法：save()——自动判断新增还是更新
    // ============================================================

    /**
     * 保存数据（自动判断新增 / 更新）
     * @param  array $data   数据
     * @param  array $where  条件（不传则新增，传入则更新）
     * @return int           新增返回 ID，更新返回影响行数
     *
     * 用法：
     *   $newId  = $db->save(['username'=>'test']);           // INSERT，返回新 ID
     *   $rows   = $db->save(['email'=>'x'], ['id'=>1]);      // UPDATE，返回影响行数
     */
    public function save(array $data, array $where = []): int
    {
        if (empty($where)) {
            return $this->table()->insertGetId($data);
        }
        return $this->table()->where($where)->update($data);
    }

    // ============================================================
    //  查 - SELECT
    // ============================================================

    /**
     * 根据主键 ID 查询单条
     */
    public function getById(int $id): ?array
    {
        $result = $this->table()->find($id);
        return $result ?: null;
    }

    /**
     * 根据条件查询单条
     * @param  array $where  如 ['username' => 'admin']
     */
    public function getOne(array $where): ?array
    {
        $result = $this->table()->where($where)->find();
        return $result ?: null;
    }

    /**
     * 根据条件查询多条
     * @param  array  $where   条件
     * @param  string $order   排序，如 'id desc'
     * @param  string $field   字段，默认全部
     */
    public function getList(array $where = [], string $order = 'id desc', string $field = '*'): array
    {
        return $this->table()
            ->where($where)
            ->order($order)
            ->field($field)
            ->select()
            ->toArray();
    }

    /**
     * 分页查询
     * @param  int    $page    页码，从 1 开始
     * @param  int    $size    每页条数
     * @param  array  $where   条件
     * @param  string $order   排序
     * @param  string $field   字段
     * @return array           ['list'=>[...], 'total'=>int, 'pages'=>int, 'page'=>int, 'size'=>int]
     */
    public function getPage(array $where = [], string $field = '*', string $order = 'id desc', int $page = 1, int $size = 10): array
    {
        $total = $this->table()->where($where)->count();
        $list  = $this->table()
            ->where($where)
            ->order($order)
            ->field($field)
            ->page($page, $size)
            ->select()
            ->toArray();

        return [
            'list'  => $list,
            'total' => $total,
            'pages' => (int) ceil($total / $size),
            'page'  => $page,
            'size'  => $size,
        ];
    }

    /**
     * 统计记录数
     */
    public function count(array $where = []): int
    {
        return $this->table()->where($where)->count();
    }

    /**
     * 字段值自增
     * @param  int    $id
     * @param  string $field  如 'view_count'
     * @param  int    $step   步长，默认 1
     */
    public function increment(int $id, string $field, int $step = 1): int
    {
        return $this->table()->where('id', $id)->inc($field, $step)->update();
    }

    /**
     * 字段值自减
     */
    public function decrement(int $id, string $field, int $step = 1): int
    {
        return $this->table()->where('id', $id)->dec($field, $step)->update();
    }

    // ============================================================
    //  删 - DELETE
    // ============================================================

    /**
     * 根据 ID 删除
     */
    public function delete(int $id): int
    {
        return $this->table()->delete($id);
    }

    /**
     * 根据条件删除
     */
    public function deleteBy(array $where): int
    {
        return $this->table()->where($where)->delete();
    }

    // ============================================================
    //  多表联查
    // ============================================================

    /**
     * 当前联表查询构造器（多次 join 调用累积到同一个查询上）
     * @var \think\db\Query|null
     */
    protected $queryBuilder = null;

    /**
     * 添加一张联表（链式调用可累积多张表，最后用 queryJoin() 结束）
     *
     * @param  string $table      要连接的表名，如 'order'
     * @param  string $alias      别名，不传则用原名（推荐传，如 'o'）
     * @param  string $condition  联表条件，如 'o.user_id = user.id'
     * @param  string $type       连接方式：LEFT | RIGHT | INNER，默认 LEFT
     * @return $this              返回自身，支持继续调用 join()
     *
     * 用法：
     *   // 单表联查
     *   $data = $db->join('order', 'o', 'o.user_id = user.id')
     *              ->queryJoin()
     *              ->field('user.id, o.order_no')
     *              ->where('user.id', 1)
     *              ->select()->toArray();
     *
     *   // 多表联查（多次 join 累积）
     *   $data = $db->join('order', 'o', 'o.user_id = user.id')
     *              ->join('product', 'p', 'o.product_id = p.id', 'INNER')
     *              ->join('category', 'c', 'p.cate_id = c.id')
     *              ->queryJoin()
     *              ->field('user.username, o.order_no, p.name, c.cate_name')
     *              ->where('user.status', 1)
     *              ->select()->toArray();
     */
    public function join(string $table, string $alias = '', string $condition = '', string $type = 'LEFT'): self
    {
        if ($this->queryBuilder === null) {
            $this->queryBuilder = $this->table();
        }

        $joinTable = $alias ? $table . ' ' . $alias : $table;
        $this->queryBuilder->join($joinTable, $condition, $type);
        return $this;
    }

    /**
     * 结束联表累积，返回查询构造器（然后可链式调用 field / where / select）
     * 调用后自动重置，下次 join() 会重新开始
     * @return \think\db\Query
     */
    public function queryJoin()
    {
        $builder = $this->queryBuilder;
        $this->queryBuilder = null;  // 重置，避免污染下一次联查
        return $builder;
    }
}
