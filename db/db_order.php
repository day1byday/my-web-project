<?php
namespace db;
class db_order extends Base {
    protected $table = 'orders';

    public function get_info($order_no)
    {
        return $this->getOne(['order_no' => $order_no]);
    }

}