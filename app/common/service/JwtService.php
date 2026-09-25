<?php
declare(strict_types=1);

namespace app\common\service;

use app\common\exception\ApiException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\facade\Config;

/**
 * JWT Access Token 签发与解析
 */
class JwtService
{
    private string $secret;
    private string $algo;
    private int $ttlAccess;

    public function __construct()
    {
        $this->secret    = (string) Config::get('jwt.secret');
        $this->algo      = (string) Config::get('jwt.algo', 'HS256');
        $this->ttlAccess = (int) Config::get('jwt.ttl_access', 900);
    }

    /**
     * 签发 Access Token
     * @param array $user 含 id / roles / security_version
     */
    public function issueAccessToken(array $user): string
    {
        $now = time();

        $payload = [
            'sub' => (int) $user['id'],
            'rol' => $user['roles'] ?? [],
            'ver' => (int) ($user['security_version'] ?? 0),
            'iss' => Config::get('jwt.issuer'),
            'aud' => Config::get('jwt.audience'),
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->ttlAccess,
            'jti' => bin2hex(random_bytes(8)),
        ];

        return JWT::encode($payload, $this->secret, $this->algo);
    }

    /**
     * 解析并校验 Access Token，返回 payload 数组
     * @throws ApiException 40101（过期）/ 40102（无效）
     */
    public function parseAccessToken(string $token): array
    {
        try {
            $payload = JWT::decode($token, new Key($this->secret, $this->algo));
            return (array) $payload;
        } catch (\Firebase\JWT\ExpiredException $e) {
            throw new ApiException(40101, '登录已过期', 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            throw new ApiException(40102, '令牌无效', 401);
        } catch (\Firebase\JWT\BeforeValidException $e) {
            throw new ApiException(40102, '令牌无效', 401);
        } catch (\Throwable $e) {
            throw new ApiException(40102, '令牌无效', 401);
        }
    }

    /**
     * 主动续期：若 token 剩余有效期不足 5 分钟，签发新 token
     */
    public function proactiveRenew(array $payload): ?string
    {
        $exp = (int) ($payload['exp'] ?? 0);
        if ($exp - time() < 300) {
            return $this->issueAccessToken([
                'id'               => (int) $payload['sub'],
                'roles'            => $payload['rol'] ?? [],
                'security_version' => (int) ($payload['ver'] ?? 0),
            ]);
        }
        return null;
    }
}
