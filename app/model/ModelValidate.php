<?php
namespace app\model;

use think\Model;
use db\db_user;
use db\db_product;
use db\db_order;

// 验证方法

class ModelValidate extends Model
{
    function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->dbUser    = new db_user();
        $this->dbProduct = new db_product();
        $this->dbOrder   = new db_order();
    }

    /**
     * 参数校验入口
     *
     * 用法示例:
     *   $validate = new ModelValidate();
     *   $result = $validate->check(
     *       ['username' => 'require|min:3|max:20', 'password' => 'require|min:6', 'email' => 'require|email'],
     *       ['username.require' => '用户名不能为空', 'password.min' => '密码至少6位'],
     *       Request::post()
     *   );
     *   if ($result['code'] !== 0) { return $result; }
     *
     * @param array $rule    校验规则，格式: ['字段名' => 'require|min:3|email']
     * @param array $message 自定义错误提示，格式: ['字段名.规则名' => '提示文字']
     * @param array $param   待校验的参数
     * @return array         通过: ['code'=>0, 'msg'=>'success']; 失败: ['code'=>4001, 'msg'=>'错误描述']
     */
    public function check(array $rule, array $message, array $param): array
    {
        // 第一步：取出规则键名，用于 isEmpty 联合校验
        $ruleKeys = array_keys($rule);

        foreach ($rule as $field => $ruleSet) {
            // 处理两种规则写法: 'require|min:3'（字符串）或 ['require', 'min:3']（数组）
            $rules = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            // 默认值：未传入的字段置为 null，避免 Undefined index
            $value = $param[$field] ?? null;

            // ----------------------------------------
            // 特殊规则: isEmpty — 所有指定字段全空才报错
            // 适用于"用户名和邮箱至少填一个"的场景
            // 写法: 'isEmpty:username,email' 放在首个字段上即可
            // ----------------------------------------
            foreach ($rules as $r) {
                if (str_starts_with($r, 'isEmpty:')) {
                    $allFields = explode(',', substr($r, 8));
                    $allEmpty  = true;
                    foreach ($allFields as $f) {
                        if (!empty($param[$f] ?? null)) {
                            $allEmpty = false;
                            break;
                        }
                    }
                    if ($allEmpty) {
                        $errorKey = $field . '.isEmpty';
                        return ['code' => 4001, 'msg' => $message[$errorKey] ?? ($field . '等字段不能全部为空')];
                    }
                }
            }

            // 遍历每条规则
            foreach ($rules as $singleRule) {
                // 拆分规则名和参数: 'min:6' → ruleName='min', params=['6']
                $ruleName   = $singleRule;
                $ruleParams = [];
                if (str_contains($singleRule, ':')) {
                    [$ruleName, $paramsStr] = explode(':', $singleRule, 2);
                    $ruleParams = explode(',', $paramsStr);
                }

                // 跳过 isEmpty（已在上层处理，它不是字段级规则）
                if ($ruleName === 'isEmpty' || $ruleName === '') {
                    continue;
                }

                // 错误消息的键名
                $errorKey = $field . '.' . $ruleName;
                $passed   = true;

                switch ($ruleName) {
                    // --------------------------------------------------
                    //  内置规则
                    // --------------------------------------------------
                    case 'require':
                        // 0 和 '0' 视为有效值
                        $passed = isset($param[$field]) && ($value !== '' && $value !== null);
                        break;

                    case 'email':
                        $passed = $this->check_email($value);
                        break;

                    case 'mobile':
                        $passed = $this->check_mobile_phone($value);
                        break;

                    case 'idcard':
                        $passed = $this->check_Id($value);
                        break;

                    case 'positive_int':
                        $passed = $this->check_positive_int($value);
                        break;

                    case 'min':
                        $min    = (int)($ruleParams[0] ?? 0);
                        $passed = mb_strlen((string)$value, 'UTF-8') >= $min;
                        break;

                    case 'max':
                        $max    = (int)($ruleParams[0] ?? 0);
                        $passed = mb_strlen((string)$value, 'UTF-8') <= $max;
                        break;

                    case 'length':
                        $len    = (int)($ruleParams[0] ?? 0);
                        $passed = mb_strlen((string)$value, 'UTF-8') === $len;
                        break;

                    case 'confirm':
                        // 确认字段: 'confirm:password' → 与 password 字段值一致
                        $targetField = $ruleParams[0] ?? '';
                        $passed = ($value === ($param[$targetField] ?? null));
                        break;

                    // --------------------------------------------------
                    //  扩展规则：自动查找本类中 check_xxx 方法
                    // --------------------------------------------------
                    default:
                        $method = 'check_' . $ruleName;
                        if (method_exists($this, $method)) {
                            $passed = $this->$method($value);
                        }
                        // 未找到对应方法的规则 → 默认放行（避免误杀）
                        break;
                }

                if (!$passed) {
                    // 优先取定制消息 → 默认消息
                    $msg = $message[$errorKey] ?? $this->defaultMessage($field, $ruleName, $ruleParams);
                    return ['code' => 4001, 'msg' => $msg];
                }
            }
        }

        return ['code' => 0, 'msg' => 'success'];
    }

    /**
     * 默认错误提示生成
     */
    private function defaultMessage(string $field, string $ruleName, array $params): string
    {
        return match ($ruleName) {
            'require'       => $field . '不能为空',
            'email'         => $field . '格式不正确',
            'mobile'        => $field . '格式不正确',
            'idcard'        => $field . '格式不正确',
            'positive_int'  => $field . '必须为正整数',
            'min'           => $field . '长度不能少于' . ($params[0] ?? '') . '位',
            'max'           => $field . '长度不能超过' . ($params[0] ?? '') . '位',
            'length'        => $field . '长度必须为' . ($params[0] ?? '') . '位',
            'confirm'       => $field . '与' . ($params[0] ?? '') . '不一致',
            default         => $field . '验证失败',
        };
    }


    /**
     * 检查参数是否是大于0的整数
     * @author lml
     * @param $param //待检测的参数
     * @return bool [格式正确返回true否则false]
     */
    public function check_positive_int($param){
        if(preg_match("/^\+?[1-9][0-9]*$/", $param)){
            return true;
        }else {
            return false;
        }
    }
    /**
     * 检查手机号格式是否正确
     * @author lml
     * @param $param [待检测的参数]
     * @return bool [格式正确返回true否则false]
     */
    public function check_mobile_phone($param){
        if(preg_match("/^(1)[0-9]{10}$/", $param)){
            return true;
        }else {
            return false;
        }
    }
    /**
     * 检查身份证格式是否正确
     * @author lml
     * @param $param [待检测的参数]
     * @return bool [格式正确返回true否则false]
     */
    public function check_Id($param){
        if(preg_match("/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|[xX])$/", $param)){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 检查邮箱格式是否正确
     * @author lml
     * @param $param [待检测的参数]
     * @return bool [格式正确返回true否则false]
     */
    public function check_email($param){
        if(preg_match("/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/", $param)){
            return true;
        }else {
            return false;
        }
    }






}