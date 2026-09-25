<?php
use think\facade\Route;

// ============================================================
// 根路由 — 处理非 API 应用请求（多应用模式回退）
// 说明：/api/* 由 app/api/route/app.php 处理；
//      其余路径（/、/ping、/app/* 前端路由）回退到此文件。
// ============================================================

// 健康检查
Route::get('ping', function () {
    return json(['code' => 0, 'message' => 'pong', 'data' => []]);
});

// Vue SPA 兜底 — 所有未匹配路由返回前端入口
Route::miss(function () {
    $file = app()->getRootPath() . 'public/app/index.html';
    if (!is_file($file)) {
        return json(['code' => 40400, 'message' => '应用未构建，请先执行 npm run build', 'data' => []]);
    }
    return response(file_get_contents($file))
        ->header(['Content-Type' => 'text/html; charset=utf-8']);
});
