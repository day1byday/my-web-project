<?php

namespace db;
class db_product extends Base
{
    protected $table = 'products';

    public function get_info($id)
    {
        return $this->getOne(['id' => $id]);
    }

}