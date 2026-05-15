<?php
namespace app\controller;
use app\BaseController;
use app\model\ModelFormat;
use think\facade\View;
use think\facade\Request;
use think\App;

class Format extends BaseController
{

    protected $formatModel;
    protected $viewModel;

    function __construct(App $app)
    {
        parent::__construct($app);
        $this->formatModel = new ModelFormat();
        $this->viewModel = new View();
    }

    /**
     * var_dump格式化
     */
    public function varDumpToArray()
    {
        // 获取原始输入数据
        $jsonInput = file_get_contents('php://input');

        // 解码 JSON 为数组
        $data = json_decode($jsonInput, true);

        return $this->formatModel->formatJsonInput($data,$jsonInput);

    }

}