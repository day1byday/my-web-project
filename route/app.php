<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;

// ============================================================
// 路由定义 - TP5 vs TP6 对比
// ============================================================
//
// 【TP5 路由】
//   Route::get('hello/:name', 'index/hello');
//   路由文件: application/route.php
//
// 【TP6 路由】
//   Route::get('hello/:name', 'index/hello');
//   路由文件: route/app.php
//   新增: 路由分组、资源路由、中间件路由
//
// ============================================================

Route::get('think', function () {
    return 'hello,ThinkPHP6!';
});

// TP5: :name 是变量占位符
// TP6: :name 保持不变，也支持 {name} 写法
Route::get('hello/:name', 'index/hello');

// ---- TP6 新增: 路由分组 ----
// TP5 不支持分组，每个路由单独写
// TP6 可以用 group 分组管理
Route::group('login', function () {
    // GET: 用户列表页（视图渲染）
    Route::get('', 'Login/index');
    // GET: 登录页面
    Route::get('loginPage', 'Login/loginPage');
    // POST: 处理登录（接收表单参数）
    Route::post('doLogin', 'Login/doLogin');
    // GET: 动态路由传参
    Route::get('profile/:id', 'Login/profile');
    // GET: 用户列表 API (JSON接口 - 供 Vue3 SPA 使用)
    Route::get('userList', 'Login/userList');
    // GET: Db 门面查询测试
    Route::get('dbTest', 'Login/dbTest');
});

// ---- TP6 新增: 资源路由 ----
// TP5: 需要手动写 7 条路由
// TP6: Route::resource('user', 'User') 一键生成
// Route::resource('user', 'User');

// ---- 生产环境: SPA 兜底 ----
// 所有未匹配路由返回 Vue3 SPA 入口，API 路由优先匹配不受影响
// 开发环境用 localhost:3000 (Vite)，此规则不干扰
Route::miss(function () {
    return response(file_get_contents(app()->getRootPath() . 'public/static/index.html'));
});
