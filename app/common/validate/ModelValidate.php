<?php
declare(strict_types=1);

namespace app\common\validate;

/**
 * 自定义参数验证器（不继承框架 Validate，返回数组而非抛异常）
 *
 * 用法：
 *   $v = new ModelValidate();
 *   $r = $v->check(
 *       ['username' => 'require|min:3|max:20', 'password' => 'require|min:6'],
 *       ['username.require' => '用户名不能为空', 'password.min' => '密码至少6位'],
 *       $params
 *   );
 *   if ($r['code'] !== 0) return $r;
 *
 * 支持规则：require / email / mobile / idcard / code / positive_int / min / max / length / confirm / isEmpty
 */
class ModelValidate
{
    /**
     * @param array $rule    校验规则，格式 ['字段名' => 'require|min:3']
     * @param array $message 自定义错误提示，格式 ['字段名.规则名' => '提示']
     * @param array $param   待校验参数
     * @return array 通过 ['code'=>0,'msg'=>'success']；失败 ['code'=>4001,'msg'=>'错误描述']
     */
    public function check(array $rule, array $message, array $param): array
    {
        $ruleKeys = array_keys($rule);

        foreach ($rule as $field => $ruleSet) {
            $rules = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            $value = $param[$field] ?? null;

            // 特殊规则：isEmpty —— 所有指定字段全空才报错
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

            foreach ($rules as $singleRule) {
                $ruleName   = $singleRule;
                $ruleParams = [];
                if (str_contains($singleRule, ':')) {
                    [$ruleName, $paramsStr] = explode(':', $singleRule, 2);
                    $ruleParams = explode(',', $paramsStr);
                }

                if ($ruleName === 'isEmpty' || $ruleName === '') {
                    continue;
                }

                $errorKey = $field . '.' . $ruleName;
                $passed   = true;

                switch ($ruleName) {
                    case 'require':
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
                    case 'code':
                        $passed = $this->check_code($value);
                        break;
                    case 'positive_int':
                        $passed = $this->check_positive_int($value);
                        break;
                    case 'min':
                        $min    = (int) ($ruleParams[0] ?? 0);
                        $passed = mb_strlen((string) $value, 'UTF-8') >= $min;
                        break;
                    case 'max':
                        $max    = (int) ($ruleParams[0] ?? 0);
                        $passed = mb_strlen((string) $value, 'UTF-8') <= $max;
                        break;
                    case 'length':
                        $len    = (int) ($ruleParams[0] ?? 0);
                        $passed = mb_strlen((string) $value, 'UTF-8') === $len;
                        break;
                    case 'confirm':
                        $targetField = $ruleParams[0] ?? '';
                        $passed = ($value === ($param[$targetField] ?? null));
                        break;

                    case 'in':
                        $passed = in_array((string) $value, $ruleParams, true);
                        break;
                    default:
                        $method = 'check_' . $ruleName;
                        if (method_exists($this, $method)) {
                            $passed = $this->$method($value);
                        }
                        break;
                }

                if (!$passed) {
                    $msg = $message[$errorKey] ?? $this->defaultMessage($field, $ruleName, $ruleParams);
                    return ['code' => 4001, 'msg' => $msg];
                }
            }
        }

        return ['code' => 0, 'msg' => 'success'];
    }

    /**
     * 默认错误提示
     */
    private function defaultMessage(string $field, string $ruleName, array $params): string
    {
        return match ($ruleName) {
            'require'      => $field . '不能为空',
            'email'        => $field . '格式不正确',
            'mobile'       => $field . '格式不正确',
            'idcard'       => $field . '格式不正确',
            'code'         => $field . '必须为6位数字',
            'positive_int' => $field . '必须为正整数',
            'min'          => $field . '长度不能少于' . ($params[0] ?? '') . '位',
            'max'          => $field . '长度不能超过' . ($params[0] ?? '') . '位',
            'length'       => $field . '长度必须为' . ($params[0] ?? '') . '位',
            'confirm'      => $field . '与' . ($params[0] ?? '') . '不一致',
            default        => $field . '验证失败',
        };
    }

    /** 6位数字验证码 */
    public function check_code($param): bool
    {
        return (bool) preg_match('/^\d{6}$/', (string) $param);
    }

    /** 正整数 */
    public function check_positive_int($param): bool
    {
        return (bool) preg_match('/^\+?[1-9][0-9]*$/', (string) $param);
    }

    /** 大陆手机号 */
    public function check_mobile_phone($param): bool
    {
        return (bool) preg_match('/^(1)[0-9]{10}$/', (string) $param);
    }

    /** 大陆身份证 */
    public function check_Id($param): bool
    {
        return (bool) preg_match('/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|[xX])$/', (string) $param);
    }

    /** 邮箱 */
    public function check_email($param): bool
    {
        return (bool) preg_match('/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])+/', (string) $param);
    }
}
