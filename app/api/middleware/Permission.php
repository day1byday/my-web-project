<?php
declare(strict_types=1);

namespace app\api\middleware;

use app\common\exception\ApiException;
use Closure;
use think\Request;
use think\Response;

/**
 * 角色权限中间件（路由中间件）
 * 需在 JwtAuth 之后执行，参数为所需角色名，如 Permission::class + ['admin']
 */
class Permission
{
    public function handle(Request $request, Closure $next, string $role = ''): Response
    {
        $roles = $request->roles ?? [];

        if (!in_array($role, $roles, true)) {
            throw new ApiException(40300, '无权限访问', 403);
        }

        return $next($request);
    }
}
