<?php
declare(strict_types=1);

namespace app\api\middleware;

use app\common\exception\ApiException;
use app\common\service\RateLimitService;
use Closure;
use think\facade\Config;
use think\Request;
use think\Response;

/**
 * 接口限流中间件（路由中间件）
 * 参数为 config/throttle.php 中的规则集名称，如 Throttle::class + ['login']
 */
class Throttle
{
    public function handle(Request $request, Closure $next, string $ruleSet = 'default'): Response
    {
        $rules = Config::get('throttle.' . $ruleSet, Config::get('throttle.default', []));
        $ip    = $request->ip();

        $limiter = new RateLimitService();

        foreach ($rules as $dimension => $cfg) {
            // cooldown 不是计数器维度，跳过（由 VerificationCodeService 处理）
            if ($dimension === 'cooldown') {
                continue;
            }

            $key = $this->buildKey($dimension, $ip, $request, $ruleSet);

            if (!$limiter->hit($key, (int) $cfg['limit'], (int) $cfg['expire'])) {
                throw new ApiException(42900, '请求过于频繁，请稍后再试', 429);
            }
        }

        return $next($request);
    }

    /**
     * 构建限流缓存键
     */
    private function buildKey(string $dimension, string $ip, Request $request, string $ruleSet): string
    {
        $base = 'throttle:' . $ruleSet . ':' . $dimension . ':';

        return match ($dimension) {
            'ip'         => $base . $ip,
            'ip_account' => $base . $ip . ':' . $this->accountIdentifier($request),
            'target'     => $base . $this->targetIdentifier($request),
            default      => $base . $ip,
        };
    }

    /**
     * 登录账号标识（用户名/邮箱/手机号）
     */
    private function accountIdentifier(Request $request): string
    {
        $id = $request->param('username', $request->param('email', $request->param('mobile', '')));
        return md5((string) $id);
    }

    /**
     * 发送验证码的目标（邮箱/手机号）
     */
    private function targetIdentifier(Request $request): string
    {
        $target = $request->param('target', '');
        return md5((string) $target);
    }
}
