<?php
/**
 * permission 表数据访问层
 */

namespace db;

class db_permission extends Base
{
    protected $table = 'permission';

    /**
     * 获取全部权限点
     */
    public function all(): array
    {
        return $this->getList([], 'id asc');
    }
}
