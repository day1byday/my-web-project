<?php
declare(strict_types=1);

namespace app\api\middleware;

use app\common\exception\ApiException;
use app\common\service\JwtService;
use Closure;
use db\db_user;
use think\Request;
use think\Response;

/**
 * JWT 鉴权中间件（路由中间件）
 * - 校验 Authorization: Bearer <token>
 * - 校验用户存在、状态正常、security_version 匹配
 * - 剩余有效期不足时主动续期，通过 X-Access-Token 响应头下发新 token
 */
class JwtAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->header('authorization', '');
        $token  = $this->extractToken($header);

        if ($token === '') {
            throw new ApiException(40100, '未登录', 401);
        }

        $jwtService = new JwtService();
        $payload    = $jwtService->parseAccessToken($token);

        $userId = (int) ($payload['sub'] ?? 0);
        if ($userId <= 0) {
            throw new ApiException(40102, '令牌无效', 401);
        }

        $dbUser = new db_user();
        $user   = $dbUser->getById($userId);
        if (!$user) {
            throw new ApiException(40100, '用户不存在', 401);
        }

        // 账号禁用
        if ((int) $user['status'] !== 1) {
            throw new ApiException(40103, '账号已禁用', 401);
        }

        // 安全版本号不匹配（密码/角色变更后即时吊销旧 token）
        if ((int) $user['security_version'] !== (int) ($payload['ver'] ?? 0)) {
            throw new ApiException(40101, '登录状态已变更，请重新登录', 401);
        }

        // 将认证信息写入请求，供控制器使用
        $request->userId = $userId;
        $request->user   = $user;
        $request->roles  = $payload['rol'] ?? [];

        // 主动续期
        $newToken = $jwtService->proactiveRenew($payload);

        $response = $next($request);

        if ($newToken) {
            $response->header(['X-Access-Token' => $newToken]);
        }

        return $response;
    }

    /**
     * 从 Authorization 头提取 Bearer token
     */
    private function extractToken(string $header): string
    {
        if (preg_match('/^Bearer\s+(.+)$/i', $header, $m)) {
            return trim($m[1]);
        }
        return '';
    }
}
