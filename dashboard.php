<?php
// dashboard.php - 主管理页面
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$jsonData = file_get_contents('instances.json');
$instances = json_decode($jsonData, true);

// 按实例类型分类
$ec2Instances = [];
$lightsailInstances = [];

foreach ($instances as $index => $instance) {
    if ($instance['INSTANCE_TYPE'] === 'ec2') {
        $ec2Instances[$index] = $instance;
    } else {
        $lightsailInstances[$index] = $instance;
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AWS实例管理仪表板</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="dashboard-container">
        <header class="dashboard-header">
            <div class="header-left">
                <h1>AWS实例管理</h1>
                <p class="subtitle">高效管理您的云资源</p>
            </div>
            <div class="header-actions">
                <button id="newConfigBtn" class="btn btn-primary">
                    <svg class="btn-icon" viewBox="0 0 24 24">
                        <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z" />
                    </svg>
                    新增配置
                </button>
                <a href="logout.php" class="btn btn-logout">
                    <svg class="btn-icon" viewBox="0 0 24 24">
                        <path d="M16,17V14H9V10H16V7L21,12L16,17M14,2A2,2 0 0,1 16,4V6H14V4H5V20H14V18H16V20A2,2 0 0,1 14,22H5A2,2 0 0,1 3,20V4A2,2 0 0,1 5,2H14Z" />
                    </svg>
                    退出
                </a>
            </div>
        </header>
        
        <div id="newConfigPanel" class="new-config-panel">
            <div class="panel-header">
                <h3>点击右上角进行AWS实例配置新增</h3>
                <button id="togglePanelBtn" class="btn btn-icon">
                    <svg viewBox="0 0 24 24">
                        <path d="M7.41,8.58L12,13.17L16.59,8.58L18,10L12,16L6,10L7.41,8.58Z" />
                    </svg>
                </button>
            </div>
            <div class="panel-content">
                <form id="newConfigForm" class="config-form">
                    <div class="form-row">
                        <div class="form-group">
                            <label>KEY_ID</label>
                            <input type="text" name="AWS_ACCESS_KEY_ID" required placeholder="AKIA...">
                        </div>
                        <div class="form-group">
                            <label>ACCESS_KEY</label>
                            <input type="text" name="AWS_SECRET_ACCESS_KEY" required placeholder="xbq...">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>实例ID/名称</label>
                            <input type="text" name="INSTANCE_ID" required placeholder="i-0...">
                        </div>
                        <div class="form-group">
                            <label>区域</label>
                            <select name="AWS_DEFAULT_REGION" class="custom-select">
                                <option value="ap-northeast-1">东京</option>
                                <option value="ap-east-1">香港</option>
                                <option value="ap-southeast-1">新加坡</option>
                                <option value="ap-northeast-2">首尔</option>
                                <option value="ap-northeast-3">大阪</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>实例类型</label>
                            <select name="INSTANCE_TYPE" class="custom-select">
                                <option value="ec2">EC2</option>
                                <option value="lightsail">Lightsail</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="reset" class="btn btn-secondary">重置</button>
                        <button type="submit" class="btn btn-primary">保存配置</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($instances); ?></div>
                <div class="stat-label">总配置数</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($lightsailInstances); ?></div>
                <div class="stat-label">Lightsail 实例</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($ec2Instances); ?></div>
                <div class="stat-label">EC2 实例</div>
            </div>
        </div>

        <section class="instance-section">
            <div class="section-header">
                <h2 class="section-title">
                    <svg class="section-icon" viewBox="0 0 24 24">
                        <path d="M7,2H17A2,2 0 0,1 19,4V20A2,2 0 0,1 17,22H7A2,2 0 0,1 5,20V4A2,2 0 0,1 7,2M7,4V9H17V4H7M7,11V13H9V11H7M11,11V13H13V11H11M15,11V13H17V11H15M7,15V17H9V15H7M11,15V17H13V15H11M15,15V17H17V15H15M7,19V20H9V19H7M11,19V20H13V19H11M15,19V20H17V19H15Z" />
                    </svg>
                    Lightsail 实例配置
                </h2>
                <div class="section-actions">
                    <div class="search-box">
                        <input type="text" placeholder="搜索实例..." id="searchLightsail">
                        <svg class="search-icon" viewBox="0 0 24 24">
                            <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="config-grid">
                <?php foreach ($lightsailInstances as $index => $instance): ?>
                    <div class="config-card">
                        <div class="card-header">
                            <h3><?php echo htmlspecialchars($instance['INSTANCE_ID']); ?></h3>
                            <span class="instance-type-badge lightsail-badge">LIGHTSAIL</span>
                        </div>
                        
                        <div class="card-body">
                            <div class="config-item">
                                <label>KEY_ID:</label>
                                <span class="sensitive-data"><?php echo htmlspecialchars(substr($instance['AWS_ACCESS_KEY_ID'], 0, 10) . '****'); ?></span>
                            </div>
                            <div class="config-item">
                                <label>ACCESS_KEY:</label>
                                <span class="sensitive-data"><?php echo htmlspecialchars(substr($instance['AWS_SECRET_ACCESS_KEY'], 0, 16) . '****'); ?></span>
                            </div>
                            <div class="config-item">
                                <label>区域:</label>
                                <span class="region-badge"><?php 
                                    switch ($instance['AWS_DEFAULT_REGION']) {
                                        case 'ap-northeast-1': echo '日本'; break;
                                        case 'ap-east-1': echo '香港'; break;
                                        case 'ap-southeast-1': echo '新加坡'; break;
                                        case 'ap-northeast-2': echo '首尔'; break;
                                        case 'ap-northeast-3': echo '大阪'; break;
                                        default: echo '未定义';
                                    }
                                    
                                ?></span>
                            </div>
                        </div>
                        
                        <div class="card-actions">
                            <button class="view-log-btn" data-instance-id="<?php echo htmlspecialchars($instance['INSTANCE_ID']); ?>">
                                <svg class="action-icon" viewBox="0 0 24 24">
                                    <path d="M3,17V19H9V17H3M3,5V7H13V5H3M13,21V19H21V17H13V15H11V21H13M7,9V11H3V13H7V15H9V9H7M21,13V11H11V13H21M15,9H17V7H21V5H17V3H15V9Z" />
                                </svg>
                                查看日志
                            </button>
                            <div class="action-buttons">
                                <button class="edit-btn" data-index="<?php echo $index; ?>">
                                    <svg class="action-icon" viewBox="0 0 24 24">
                                        <path d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z" />
                                    </svg>
                                </button>
                                <button class="delete-btn" data-index="<?php echo $index; ?>" data-instance-id="<?php echo htmlspecialchars($instance['INSTANCE_ID']); ?>">
                                    <svg class="action-icon" viewBox="0 0 24 24">
                                        <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($lightsailInstances)): ?>
                    <div class="empty-state">
                        <svg class="empty-icon" viewBox="0 0 24 24">
                            <path d="M20 6H16V4C16 2.9 15.1 2 14 2H10C8.9 2 8 2.9 8 4V6H4C2.9 6 2 6.9 2 8V20C2 21.1 2.9 22 4 22H20C21.1 22 22 21.1 22 20V8C22 6.9 21.1 6 20 6M10 4H14V6H10V4M20 20H4V8H20V20M16 15C16 16.66 14.66 18 13 18S10 16.66 10 15V11H16V15M12 13V15C12 15.55 12.45 16 13 16S14 15.55 14 15V13H12Z" />
                        </svg>
                        <h3>没有Lightsail实例配置</h3>
                        <p>点击上方"新增配置"按钮添加Lightsail实例</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>


        <section class="instance-section">
            <div class="section-header">
                <h2 class="section-title">
                    <svg class="section-icon" viewBox="0 0 24 24">
                        <path d="M12,15.5A3.5,3.5 0 0,1 8.5,12A3.5,3.5 0 0,1 12,8.5A3.5,3.5 0 0,1 15.5,12A3.5,3.5 0 0,1 12,15.5M19.43,12.97C19.47,12.65 19.5,12.33 19.5,12C19.5,11.67 19.47,11.34 19.43,11L21.54,9.37C21.73,9.22 21.78,8.95 21.66,8.73L19.66,5.27C19.54,5.05 19.27,4.96 19.05,5.05L16.56,6.05C16.04,5.66 15.5,5.32 14.87,5.07L14.5,2.42C14.46,2.18 14.25,2 14,2H10C9.75,2 9.54,2.18 9.5,2.42L9.13,5.07C8.5,5.32 7.96,5.66 7.44,6.05L4.95,5.05C4.73,4.96 4.46,5.05 4.34,5.27L2.34,8.73C2.21,8.95 2.27,9.22 2.46,9.37L4.57,11C4.53,11.34 4.5,11.67 4.5,12C4.5,12.33 4.53,12.65 4.57,12.97L2.46,14.63C2.27,14.78 2.21,15.05 2.34,15.27L4.34,18.73C4.46,18.95 4.73,19.03 4.95,18.95L7.44,17.94C7.96,18.34 8.5,18.68 9.13,18.93L9.5,21.58C9.54,21.82 9.75,22 10,22H14C14.25,22 14.46,21.82 14.5,21.58L14.87,18.93C15.5,18.67 16.04,18.34 16.56,17.94L19.05,18.95C19.27,19.03 19.54,18.95 19.66,18.73L21.66,15.27C21.78,15.05 21.73,14.78 21.54,14.63L19.43,12.97Z" />
                    </svg>
                    EC2 实例配置
                </h2>
                <div class="section-actions">
                    <div class="search-box">
                        <input type="text" placeholder="搜索实例..." id="searchEC2">
                        <svg class="search-icon" viewBox="0 0 24 24">
                            <path d="M9.5,3A6.5,6.5 0 0,1 16,9.5C16,11.11 15.41,12.59 14.44,13.73L14.71,14H15.5L20.5,19L19,20.5L14,15.5V14.71L13.73,14.44C12.59,15.41 11.11,16 9.5,16A6.5,6.5 0 0,1 3,9.5A6.5,6.5 0 0,1 9.5,3M9.5,5C7,5 5,7 5,9.5C5,12 7,14 9.5,14C12,14 14,12 14,9.5C14,7 12,5 9.5,5Z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="config-grid">
                <?php foreach ($ec2Instances as $index => $instance): ?>
                    <div class="config-card">
                        <div class="card-header">
                            <h3><?php echo htmlspecialchars($instance['INSTANCE_ID']); ?></h3>
                            <span class="instance-type-badge ec2-badge">EC2</span>
                        </div>
                        
                        <div class="card-body">
                            <div class="config-item">
                                <label>KEY_ID:</label>
                                <span class="sensitive-data"><?php echo htmlspecialchars(substr($instance['AWS_ACCESS_KEY_ID'], 0, 10) . '****'); ?></span>
                            </div>
                            <div class="config-item">
                                <label>ACCESS_KEY:</label>
                                <span class="sensitive-data"><?php echo htmlspecialchars(substr($instance['AWS_SECRET_ACCESS_KEY'], 0, 16) . '****'); ?></span>
                            </div>
                            <div class="config-item">
                                <label>区域:</label>
                                <span class="region-badge"><?php 
                                    switch ($instance['AWS_DEFAULT_REGION']) {
                                        case 'ap-northeast-1': echo '日本'; break;
                                        case 'ap-east-1': echo '香港'; break;
                                        case 'ap-southeast-1': echo '新加坡'; break;
                                        default: echo '其他';
                                    }
                                ?></span>
                            </div>
                        </div>
                        
                        <div class="card-actions">
                            <button class="view-log-btn" data-instance-id="<?php echo htmlspecialchars($instance['INSTANCE_ID']); ?>">
                                <svg class="action-icon" viewBox="0 0 24 24">
                                    <path d="M3,17V19H9V17H3M3,5V7H13V5H3M13,21V19H21V17H13V15H11V21H13M7,9V11H3V13H7V15H9V9H7M21,13V11H11V13H21M15,9H17V7H21V5H17V3H15V9Z" />
                                </svg>
                                查看日志
                            </button>
                            <div class="action-buttons">
                                <button class="edit-btn" data-index="<?php echo $index; ?>">
                                    <svg class="action-icon" viewBox="0 0 24 24">
                                        <path d="M20.71,7.04C21.1,6.65 21.1,6 20.71,5.63L18.37,3.29C18,2.9 17.35,2.9 16.96,3.29L15.12,5.12L18.87,8.87M3,17.25V21H6.75L17.81,9.93L14.06,6.18L3,17.25Z" />
                                    </svg>
                                </button>
                                <button class="delete-btn" data-index="<?php echo $index; ?>" data-instance-id="<?php echo htmlspecialchars($instance['INSTANCE_ID']); ?>">
                                    <svg class="action-icon" viewBox="0 0 24 24">
                                        <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php if (empty($ec2Instances)): ?>
                    <div class="empty-state">
                        <svg class="empty-icon" viewBox="0 0 24 24">
                            <path d="M20 6H16V4C16 2.9 15.1 2 14 2H10C8.9 2 8 2.9 8 4V6H4C2.9 6 2 6.9 2 8V20C2 21.1 2.9 22 4 22H20C21.1 22 22 21.1 22 20V8C22 6.9 21.1 6 20 6M10 4H14V6H10V4M20 20H4V8H20V20M16 15C16 16.66 14.66 18 13 18S10 16.66 10 15V11H16V15M12 13V15C12 15.55 12.45 16 13 16S14 15.55 14 15V13H12Z" />
                        </svg>
                        <h3>没有EC2实例配置</h3>
                        <p>点击上方"新增配置"按钮添加EC2实例</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
    
    <!-- 日志查看模态框 -->
    <div id="logModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>实例日志查看器</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="log-header">
                    <h4 id="logInstanceId">实例名称日志</h4>
                    <div class="log-info">
                        <span>最后25行</span>
                    </div>
                </div>
                <div class="log-container">
                    <pre id="logContent">正在加载日志...</pre>
                </div>
            </div>
        </div>
    </div>

    <!-- 编辑配置模态框 -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>编辑实例配置</h3>
                <span class="close-edit">&times;</span>
            </div>
            <div class="modal-body">
                <form id="editConfigForm" class="config-form">
                    <input type="hidden" name="original_index" id="editOriginalIndex">
                    <div class="form-row">
                        <div class="form-group">
                            <label>KEY_ID</label>
                            <input type="text" name="AWS_ACCESS_KEY_ID" id="editKeyId" required>
                        </div>
                        <div class="form-group">
                            <label>ACCESS_KEY</label>
                            <input type="text" name="AWS_SECRET_ACCESS_KEY" id="editAccessKey" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>实例ID/名称</label>
                            <input type="text" name="INSTANCE_ID" id="editInstanceId" required>
                        </div>
                        <div class="form-group">
                            <label>区域</label>
                            <select name="AWS_DEFAULT_REGION" id="editRegion" class="custom-select">
                                <option value="ap-northeast-1">东京</option>
                                <option value="ap-east-1">香港</option>
                                <option value="ap-southeast-1">新加坡</option>
                                <option value="ap-northeast-2">首尔</option>
                                <option value="ap-northeast-3">大阪</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>类型</label>
                            <select name="INSTANCE_TYPE" id="editType" class="custom-select">
                                <option value="ec2">EC2</option>
                                <option value="lightsail">Lightsail</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="btn btn-secondary close-edit">取消</button>
                        <button type="submit" class="btn btn-primary">更新配置</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // 全局变量，包含所有实例配置
        const allInstances = <?php echo json_encode($instances); ?>;
    </script>
    <script src="dashboard.js"></script>
</body>
</html>