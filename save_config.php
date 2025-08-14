<?php
// save_config.php - 保存配置API
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('HTTP/1.1 403 Forbidden');
    die('未授权访问');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_GET['action'] ?? '';
    $configFile = 'instances.json';
    
    if (file_exists($configFile)) {
        $jsonData = file_get_contents($configFile);
        $instances = json_decode($jsonData, true);
    } else {
        $instances = [];
    }
    
    if ($action === 'create') {
        // 新增配置
        $newInstance = [
            'AWS_ACCESS_KEY_ID' => $_POST['AWS_ACCESS_KEY_ID'],
            'AWS_SECRET_ACCESS_KEY' => $_POST['AWS_SECRET_ACCESS_KEY'],
            'AWS_DEFAULT_REGION' => $_POST['AWS_DEFAULT_REGION'],
            'INSTANCE_ID' => $_POST['INSTANCE_ID'],
            'INSTANCE_TYPE' => $_POST['INSTANCE_TYPE']
        ];
        
        $instances[] = $newInstance;
        
        file_put_contents($configFile, json_encode($instances, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
    } 
    elseif ($action === 'update') {
        // 更新配置
        $index = $_GET['index'] ?? null;
        
        if ($index === null || !isset($instances[$index])) {
            echo json_encode(['success' => false, 'message' => '索引无效']);
            exit;
        }
        
        $updatedInstance = [
            'AWS_ACCESS_KEY_ID' => $_POST['AWS_ACCESS_KEY_ID'],
            'AWS_SECRET_ACCESS_KEY' => $_POST['AWS_SECRET_ACCESS_KEY'],
            'AWS_DEFAULT_REGION' => $_POST['AWS_DEFAULT_REGION'],
            'INSTANCE_ID' => $_POST['INSTANCE_ID'],
            'INSTANCE_TYPE' => $_POST['INSTANCE_TYPE']
        ];
        
        $instances[$index] = $updatedInstance;
        
        file_put_contents($configFile, json_encode($instances, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
    } 
    elseif ($action === 'delete') {
        // 删除配置
        $index = $_GET['index'] ?? null;
        
        if ($index === null || !isset($instances[$index])) {
            echo json_encode(['success' => false, 'message' => '索引无效']);
            exit;
        }
        
        // 从数组中删除指定索引的配置
        array_splice($instances, $index, 1);
        
        file_put_contents($configFile, json_encode($instances, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true]);
    } 
    else {
        echo json_encode(['success' => false, 'message' => '无效操作']);
    }
} else {
    header('HTTP/1.1 400 Bad Request');
    die('无效请求');
}