@echo off
chcp 65001 >nul
echo ========================================
echo  环境变量配置工具（一次性设置）
echo ========================================
echo.
echo 正在设置 PHPRC 环境变量（用户级别）...
echo 这样 PHP 所有进程都能找到 php.ini
echo.

:: 设置用户级别的 PHPRC 环境变量
setx PHPRC "C:\php-8.1.33-x64"

echo.
echo 设置完成！请关闭并重新打开所有终端窗口。
echo 然后运行 start.cmd 启动服务器。
echo.
pause
