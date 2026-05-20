<?php
/**
 * DDZ 斗地主 - 登录页面
 * 使用 PDO 预处理语句 + password_verify，消除 SQL 注入和明文密码问题
 * 
 * 修复: P0#1 (SQL注入), P0#4 (明文密码)
 */
session_start();

// 已登录则直接跳转
if (isset($_SESSION['id']) && !empty($_SESSION['id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/response.php';

$config = require __DIR__ . '/config.php';
DB::init($config['db']);

$error = '';
if (isset($_POST['ddz_user']) && $_POST['ddz_pwd']) {
    $user = trim($_POST['ddz_user']);
    $pwd  = $_POST['ddz_pwd'];

    if ($user !== '' && $pwd !== '') {
        $row = DB::selectOne(
            'SELECT * FROM player WHERE mail = ?',
            [$user]
        );

        $authenticated = false;
        if ($row) {
            // 优先使用 password_hash 验证（已迁移的用户）
            if (!empty($row['password_hash'])) {
                $authenticated = password_verify($pwd, $row['password_hash']);
            } 
            // 回退到明文密码验证（未迁移用户，临时兼容）
            elseif (!empty($row['password']) && $row['password'] === $pwd) {
                $authenticated = true;
                // 静默迁移：更新 password_hash
                $hash = password_hash($pwd, PASSWORD_BCRYPT);
                DB::execute('UPDATE player SET password_hash = ? WHERE id = ?', [$hash, $row['id']]);
            }
        }

        if ($authenticated) {
            setLoginSession((int)$row['id'], $row['name'], [
                'duanwei' => $row['duanwei'] ?? '',
                'chengwei' => $row['chengwei'] ?? '',
                'lihui'   => $row['lihui'] ?? '',
                'bglihui' => $row['bglihui'] ?? '',
                'touxiang' => $row['touxiang'] ?? '',
                'jinbi'   => $row['jinbi'] ?? 0,
                'hunyu'   => $row['hunyu'] ?? 0,
            ]);
            header('Location: index.php');
            exit;
        } else {
            $error = '账号或密码错误！';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en-US">
<title>斗地主 | madmic</title>
    <meta charset="UTF-8">

<head>
<script type="application/javascript" src="js/jquery-1.8.3.min.js"></script>
<script type="application/javascript" src="js/yinghua.js"></script>

<style>
   input::-webkit-input-placeholder { /* Chrome/Opera/Safari */
    color: white;
  }
</style>
</head>
        <body style="background: url('img/ui/blackbg.png')no-repeat; background-size: 100% 100%;height: 100%;background-attachment:fixed;overflow:hidden;" >   
          <div style="background: url('img/ui/LOGO_CHST.png'); background-size: 100% 100%;height: 50%;width: 45%;position: absolute;top: 10%;left: 15%;">
          </div>
          <div>
            <form method="post" action="">
            <input type="text" name="ddz_user" style="background-color: rgb(89, 102, 127);width: 10%;height: 3%;position: absolute;top: 30%;left: 55%;color:white;" placeholder="账号/邮箱" >
            <input type="password" name="ddz_pwd" style="background-color: rgb(89, 102, 127);width: 10%;height: 3%;position: absolute;top: 35%;left: 55%;color:white;" placeholder="密码" > 
            <button type="submit" name="ddz" style="background: url('img/ui/login.png')no-repeat;background-size: 100% 100%;width: 10%;height: 5%;position: absolute;top: 40%;left: 55%;"></button>
        </form>
          </div>

          <?php if ($error): ?>
          <script type="text/javascript">alert('<?php echo addslashes($error); ?>');</script>
          <?php endif; ?>
        </body>
</html>