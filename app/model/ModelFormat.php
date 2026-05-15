<?php
namespace app\model;

use think\Model;
use db\db_user;
use db\db_product;
use db\db_order;

class ModelFormat extends Model
{
    function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->dbUser    = new db_user();
        $this->dbProduct = new db_product();
        $this->dbOrder   = new db_order();
    }

    /**
     * 接收前端 JSON 数据并格式化输出
     */
    function formatJsonInput($data,$jsonInput): array
    {
        // 检查 JSON 是否有效
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => true,
                'message' => 'Invalid JSON: ' . json_last_error_msg(),
                'raw_input' => $jsonInput
            ];
        }

        return [
            'error' => false,
            'data' => $data,
            'formatted' => json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        ];
    }
}