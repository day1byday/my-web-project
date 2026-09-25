<?php
declare(strict_types=1);

namespace app\common\service;

use app\common\exception\ApiException;
use db\db_user;
use db\db_user_role;
use think\facade\Config;
use think\facade\Cookie;

/**
 * 认证业务逻辑（注册 / 登录 / 刷新 / 登出 / 发送验证码）
 */
class AuthService
{
    private db_user $dbUser;
    private JwtService $jwt;
    private TokenService $token;

    public function __construct()
    {
        $this->dbUser = new db_user();
        $this->jwt    = new JwtService();
        $this->token  = new TokenService();
    }

    /**
     * 发送验证码
     */
    public function sendCode(string $scene, string $channel, string $target, string $ip): array
    {
        $service = new VerificationCodeService();
        $code    = $service->send($scene, $channel, $target, $ip);

        $data = [];
        if ($code !== null) {
            $data['debug_code'] = $code; // 仅调试模式返回
        }

        return $data;
    }

    /**
     * 注册
     */
    public function register(array $data, string $ip): array
    {
        // 唯一性检查
        if ($data['username'] !== '' && $this->dbUser->findByUsername($data['username'])) {
            throw new ApiException(40900, '用户名已存在', 409);
        }
        if (!empty($data['email']) && $this->dbUser->findByEmail($data['email'])) {
            throw new ApiException(40900, '该邮箱已被注册', 409);
        }
        if (!empty($data['mobile']) && $this->dbUser->findByMobile($data['mobile'])) {
            throw new ApiException(40900, '该手机号已被注册', 409);
        }

        // 验证码校验（按渠道）
        $channel = !empty($data['email']) ? 'email' : 'mobile';
        $target  = $channel === 'email' ? $data['email'] : $data['mobile'];
        (new VerificationCodeService())->verify('register', $target, (string) $data['code']);

        // 创建用户
        $userId = $this->dbUser->save([
            'username'         => $data['username'],
            'email'            => !empty($data['email']) ? $data['email'] : null,
            'mobile'           => !empty($data['mobile']) ? $data['mobile'] : null,
            'password'         => password_hash($data['password'], PASSWORD_DEFAULT),
            'nickname'         => $data['username'],
            'status'           => 1,
            'email_verified'   => !empty($data['email']) ? 1 : 0,
            'mobile_verified'  => !empty($data['mobile']) ? 1 : 0,
            'last_login_ip'    => $ip,
            'last_login_time'  => date('Y-m-d H:i:s'),
        ]);

        // 分配默认角色
        (new db_user_role())->assignDefault($userId, 'user');

        return ['id' => $userId, 'username' => $data['username']];
    }

    /**
     * 登录
     */
    public function login(string $identifier, string $password, string $ip, string $ua): array
    {
        $user = $this->dbUser->findByLogin($identifier);

        // 不泄露"用户不存在"与"密码错误"的区别
        if (!$user) {
            throw new ApiException(40102, '用户名或密码错误', 401);
        }

        // 锁定检查
        if ($this->dbUser->isLocked($user)) {
            throw new ApiException(40105, '账号已锁定，请稍后再试', 401);
        }

        // 密码校验
        if (!password_verify($password, (string) $user['password'])) {
            $this->dbUser->increaseFailedLogin((int) $user['id'], 5, 15);
            throw new ApiException(40102, '用户名或密码错误', 401);
        }

        // 账号禁用
        if ((int) $user['status'] !== 1) {
            throw new ApiException(40103, '账号已禁用', 401);
        }

        // 登录成功：重置失败计数 + 更新登录信息
        $this->dbUser->resetFailedLogin((int) $user['id']);
        $this->dbUser->updateLogin((int) $user['id'], $ip);

        // 签发令牌
        $roles = (new db_user_role())->roleNamesForUser((int) $user['id']);
        $user['roles'] = $roles;

        $accessToken  = $this->jwt->issueAccessToken($user);
        $refreshToken = $this->token->issue((int) $user['id'], $ip, $ua);

        // 设置 httpOnly 刷新令牌 Cookie
        $this->setRefreshCookie($refreshToken);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type'    => 'Bearer',
            'expires_in'    => (int) Config::get('jwt.ttl_access', 900),
            'user'          => $this->safeUser($user),
        ];
    }

    /**
     * 刷新令牌（轮换）
     */
    public function refresh(string $refreshPlain, string $ip, string $ua): array
    {
        $result = $this->token->rotate($refreshPlain, $ip, $ua);

        $user = $this->dbUser->getById($result['user_id']);
        if (!$user || (int) $user['status'] !== 1) {
            throw new ApiException(40100, '用户不存在或已禁用', 401);
        }

        $roles = (new db_user_role())->roleNamesForUser((int) $user['id']);
        $user['roles'] = $roles;

        $accessToken = $this->jwt->issueAccessToken($user);

        // 设置新的 httpOnly 刷新令牌 Cookie
        $this->setRefreshCookie($result['plain']);

        return [
            'access_token'  => $accessToken,
            'refresh_token' => $result['plain'],
            'token_type'    => 'Bearer',
            'expires_in'    => (int) Config::get('jwt.ttl_access', 900),
        ];
    }

    /**
     * 登出（撤销当前刷新令牌）
     */
    public function logout(string $refreshPlain): void
    {
        $this->token->revokeCurrent($refreshPlain);
        Cookie::delete('refresh_token', ['path' => '/api/v1/auth']);
    }

    /**
     * 忘记密码：发送重置验证码
     * 防用户枚举：无论账号是否存在，均返回相同提示
     */
    public function forgotPassword(string $channel, string $target, string $ip): array
    {
        // 仅当账号存在时才真正发送验证码
        $user = $channel === 'email'
            ? $this->dbUser->findByEmail($target)
            : $this->dbUser->findByMobile($target);

        $data = [];
        if ($user) {
            $service = new VerificationCodeService();
            $code    = $service->send('reset_password', $channel, $target, $ip);
            if ($code !== null) {
                $data['debug_code'] = $code; // 仅调试模式返回
            }
        }

        return $data;
    }

    /**
     * 重置密码（需验证码）
     */
    public function resetPassword(string $channel, string $target, string $code, string $newPassword): void
    {
        // 校验验证码
        (new VerificationCodeService())->verify('reset_password', $target, (string) $code);

        // 查找用户
        $user = $channel === 'email'
            ? $this->dbUser->findByEmail($target)
            : $this->dbUser->findByMobile($target);

        if (!$user) {
            throw new ApiException(40400, '账号不存在', 404);
        }

        // 更新密码
        $this->dbUser->save([
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ], ['id' => (int) $user['id']]);

        // 安全版本号递增 + 吊销全部会话（强制重新登录）
        $this->dbUser->bumpSecurityVersion((int) $user['id']);
        $this->token->revokeAllForUser((int) $user['id']);
    }

    /**
     * 设置 httpOnly 刷新令牌 Cookie
     */
    private function setRefreshCookie(string $refreshToken): void
    {
        Cookie::set('refresh_token', $refreshToken, [
            'expire'   => (int) Config::get('jwt.ttl_refresh', 604800),
            'path'     => '/api/v1/auth',
            'httponly' => true,
            'samesite' => 'lax',
            'secure'   => false, // 生产环境需开启（HTTPS）
        ]);
    }

    /**
     * 脱敏用户信息（去除密码等敏感字段）
     */
    private function safeUser(array $user): array
    {
        return [
            'id'               => (int) $user['id'],
            'username'         => $user['username'],
            'email'            => $user['email'] ?? '',
            'mobile'           => $user['mobile'] ?? '',
            'nickname'         => $user['nickname'] ?? '',
            'avatar'           => $user['avatar'] ?? '',
            'roles'            => $user['roles'] ?? [],
            'last_login_time'  => $user['last_login_time'] ?? '',
        ];
    }
}
