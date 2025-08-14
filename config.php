<?php
// 检查是否直接访问config.php
if (basename($_SERVER['PHP_SELF']) == 'config.php') {
    die('禁止访问');
}

$login_password = "123"; // 替换为实际密码
?>