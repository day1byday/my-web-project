<?php
namespace app\model;

// 格式处理工具类，不继承 Model，不关联数据库表

class ModelFormat
{

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