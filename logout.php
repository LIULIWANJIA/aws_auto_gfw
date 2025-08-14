<?php
// logout.php - 退出登录
session_start();
session_destroy();
header('Location: index.php');
exit;