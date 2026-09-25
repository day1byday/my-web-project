<?php
/**
 * role 表数据访问层
 */

namespace db;

class db_role extends Base
{
    protected $table = 'role';

    /**
     * 按名称查找角色
     */
    public function findByName(string $name): ?array
    {
        return $this->getOne(['name' => $name]);
    }

    /**
     * 获取全部启用角色
     */
    public function allActive(): array
    {
        return $this->getList(['status' => 1], 'id asc');
    }
}
