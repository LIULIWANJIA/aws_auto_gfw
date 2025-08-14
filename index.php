<?php
// index.php - 登录页面
session_start();
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    require_once('config.php');
    
    if ($_POST['password'] === $login_password) {
        $_SESSION['logged_in'] = true;
        header('Location: dashboard.php');
        exit;
    } else {
        $login_error = "密码错误，请重试";
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWS实例管理系统 - 登录</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-header">
            <h1>AWS实例管理系统</h1>
            <p>安全访问您的云资源</p>
        </div>
        <form method="POST" class="login-form">
            <div class="input-group">
                <svg class="input-icon" viewBox="0 0 24 24">
                    <path d="M12,17A2,2 0 0,0 14,15C14,13.89 13.1,13 12,13A2,2 0 0,0 10,15A2,2 0 0,0 12,17M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6A2,2 0 0,1 4,20V10C4,8.89 4.9,8 6,8H7V6A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,3A3,3 0 0,0 9,6V8H15V6A3,3 0 0,0 12,3Z" />
                </svg>
                <input type="password" name="password" placeholder="输入密码" required class="login-input">
            </div>
            <?php if (isset($login_error)): ?>
                <div class="error-message"><?php echo $login_error; ?></div>
            <?php endif; ?>
            <button type="submit" class="login-button">登录系统</button>
        </form>
        <div class="login-footer">
            <p>© 2023 AWS实例管理器 | 安全云资源管理</p>
        </div>
    </div>
</body>
</html>