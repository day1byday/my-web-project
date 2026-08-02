<?php
use think\facade\Route;

// ============================================================
// 路由定义
// ============================================================

// 健康检查
Route::get('ping', function () {
    return json(['code' => 0, 'msg' => 'pong']);
});

// Vue SPA 兜底 — 所有未匹配路由返回前端入口
Route::miss(function () {
    $file = app()->getRootPath() . 'public/app/index.html';
    if (!is_file($file)) {
        return response('应用未构建，请先执行 npm run build', 404);
    }
    return response(file_get_contents($file))
        ->header(['Content-Type' => 'text/html; charset=utf-8']);
});
