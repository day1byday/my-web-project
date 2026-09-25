<?php
use think\facade\Route;
use app\api\middleware\JwtAuth;
use app\api\middleware\Permission;
use app\api\middleware\Throttle;

// ============================================================
// API 路由（多应用模式：/api 前缀已由 MultiApp 剥离）
// 实际 URL：/api/v1/auth/login 等
// ============================================================

// ---------- 认证（公开接口） ----------
Route::group('v1/auth', function () {
    // 发送验证码
    Route::post('send-code', 'v1.Auth/sendCode')->middleware(Throttle::class, 'send_code');
    // 注册
    Route::post('register', 'v1.Auth/register')->middleware(Throttle::class, 'send_code');
    // 登录
    Route::post('login', 'v1.Auth/login')->middleware(Throttle::class, 'login');
    // 刷新令牌
    Route::post('refresh', 'v1.Auth/refresh')->middleware(Throttle::class, 'refresh');
    // 登出
    Route::post('logout', 'v1.Auth/logout');
    // 忘记密码（发送重置验证码）
    Route::post('forgot-password', 'v1.Auth/forgotPassword')->middleware(Throttle::class, 'send_code');
    // 重置密码
    Route::post('reset-password', 'v1.Auth/resetPassword')->middleware(Throttle::class, 'send_code');
});

// ---------- 用户（需登录） ----------
Route::group('v1/user', function () {
    Route::get('profile', 'v1.User/profile');
    Route::put('profile', 'v1.User/updateProfile');
    Route::put('password', 'v1.User/changePassword');
    Route::put('email', 'v1.User/bindEmail');
    Route::put('mobile', 'v1.User/bindMobile');
})->middleware(JwtAuth::class);

// ---------- 管理后台（需管理员角色） ----------
Route::group('v1/admin', function () {
    Route::get('users', 'v1.admin.UserAdmin/list');
    Route::get('users/:id', 'v1.admin.UserAdmin/detail')->pattern(['id' => '\d+']);
    Route::put('users/:id/status', 'v1.admin.UserAdmin/status');
    Route::put('users/:id/roles', 'v1.admin.UserAdmin/roles');

    Route::get('roles', 'v1.admin.RoleAdmin/list');
    Route::post('roles', 'v1.admin.RoleAdmin/create');
    Route::put('roles/:id', 'v1.admin.RoleAdmin/update');
    Route::delete('roles/:id', 'v1.admin.RoleAdmin/delete');

    Route::get('permissions', 'v1.admin.PermissionAdmin/list');
    Route::put('roles/:id/permissions', 'v1.admin.RoleAdmin/permissions');
})->middleware([JwtAuth::class, [Permission::class, ['admin']]]);
