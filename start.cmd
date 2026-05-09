@echo off
chcp 65001 >nul
echo ========================================
echo  ThinkPHP 6 开发服务器启动器
echo ========================================
echo.

:: 方案A：设置 PHPRC 环境变量（推荐）
:: 让 PHP 子进程能找到 php.ini 中的 pdo_mysql 配置
set PHPRC=C:\php-8.1.33-x64

:: 启动内置服务器
C:\php-8.1.33-x64\php.exe -S 0.0.0.0:8001 -t public public\router.php

echo.
echo 服务器已关闭。
pause
