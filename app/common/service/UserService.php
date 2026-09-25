<?php
declare(strict_types=1);

namespace app\common\service;

use app\common\exception\ApiException;
use db\db_user;
use db\db_user_role;

/**
 * 用户业务逻辑（个人信息 / 密码 / 绑定邮箱手机）
 */
class UserService
{
    private db_user $dbUser;

    public function __construct()
    {
        $this->dbUser = new db_user();
    }

    /**
     * 获取个人信息（含角色）
     */
    public function profile(int $userId): array
    {
        $user = $this->dbUser->getById($userId);
        if (!$user) {
            throw new ApiException(40400, '用户不存在', 404);
        }

        $user['roles'] = (new db_user_role())->roleNamesForUser($userId);

        return $this->safeUser($user);
    }

    /**
     * 更新个人信息（昵称/头像）
     */
    public function updateProfile(int $userId, array $data): array
    {
        $update = [];
        if (isset($data['nickname'])) {
            $update['nickname'] = $data['nickname'];
        }
        if (isset($data['avatar'])) {
            $update['avatar'] = $data['avatar'];
        }

        if (empty($update)) {
            throw new ApiException(40000, '无可更新字段', 400);
        }

        $this->dbUser->save($update, ['id' => $userId]);

        return $this->profile($userId);
    }

    /**
     * 修改密码（验证旧密码，改后吊销全部会话）
     */
    public function changePassword(int $userId, string $oldPassword, string $newPassword): void
    {
        $user = $this->dbUser->getById($userId);
        if (!$user) {
            throw new ApiException(40400, '用户不存在', 404);
        }

        if (!password_verify($oldPassword, (string) $user['password'])) {
            throw new ApiException(40000, '原密码错误', 400);
        }

        $this->dbUser->save([
            'password' => password_hash($newPassword, PASSWORD_DEFAULT),
        ], ['id' => $userId]);

        // 安全版本号递增，吊销所有旧 token
        $this->dbUser->bumpSecurityVersion($userId);
        (new TokenService())->revokeAllForUser($userId);
    }

    /**
     * 绑定/修改邮箱（需验证码）
     */
    public function bindEmail(int $userId, string $email, string $code): void
    {
        // 邮箱唯一性
        if ($this->dbUser->findByEmail($email)) {
            throw new ApiException(40900, '该邮箱已被使用', 409);
        }

        (new VerificationCodeService())->verify('bind_email', $email, $code);

        $this->dbUser->save(['email' => $email, 'email_verified' => 1], ['id' => $userId]);
        $this->dbUser->bumpSecurityVersion($userId);
    }

    /**
     * 绑定/修改手机号（需验证码）
     */
    public function bindMobile(int $userId, string $mobile, string $code): void
    {
        if ($this->dbUser->findByMobile($mobile)) {
            throw new ApiException(40900, '该手机号已被使用', 409);
        }

        (new VerificationCodeService())->verify('bind_mobile', $mobile, $code);

        $this->dbUser->save(['mobile' => $mobile, 'mobile_verified' => 1], ['id' => $userId]);
        $this->dbUser->bumpSecurityVersion($userId);
    }

    /**
     * 脱敏用户信息
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
            'email_verified'   => (int) ($user['email_verified'] ?? 0),
            'mobile_verified'  => (int) ($user['mobile_verified'] ?? 0),
            'roles'            => $user['roles'] ?? [],
            'last_login_time'  => $user['last_login_time'] ?? '',
            'create_time'      => $user['create_time'] ?? '',
        ];
    }
}
