<?php
// get_log.php - 获取日志API
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    die('未授权访问');
}

if (isset($_GET['instanceId'])) {
    $instanceId = $_GET['instanceId'];
    $logFile = "Logs/{$instanceId}.log";
    
    if (file_exists($logFile)) {
        // 读取文件最后300行
        $lines = [];
        $fp = fopen($logFile, 'r');
        $pos = -1;
        $currentLine = '';
        $lineCount = 0;
        
        // 从文件末尾开始读取
        while (fseek($fp, $pos, SEEK_END) !== -1 && $lineCount < 25) {
            $char = fgetc($fp);
            if ($char === "\n") {
                $lines[] = $currentLine;
                $currentLine = '';
                $lineCount++;
            } else {
                $currentLine = $char . $currentLine;
            }
            $pos--;
        }
        
        if (!empty($currentLine)) {
            $lines[] = $currentLine;
        }
        
        fclose($fp);
        
        // 反转数组并输出
        $lines = array_reverse($lines);
        echo htmlspecialchars(implode("\n", $lines));
    } else {
        echo "暂无日志";
    }
} else {
    echo "需要提供实例ID";
}