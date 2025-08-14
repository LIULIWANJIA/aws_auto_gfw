<div class="config-card">
    <div class="card-header">
        <h3><?php echo htmlspecialchars($instance['INSTANCE_ID']); ?></h3>
        <span class="instance-type-badge"><?php echo strtoupper($instance['INSTANCE_TYPE']); ?></span>
    </div>
    
    <div class="card-body">
        <div class="config-item">
            <label>KEY_ID:</label>
            <span><?php echo htmlspecialchars(substr($instance['AWS_ACCESS_KEY_ID'], 0, 12) . '****'); ?></span>
        </div>
        <div class="config-item">
            <label>ACCESS_KEY:</label>
            <span><?php echo htmlspecialchars(substr($instance['AWS_SECRET_ACCESS_KEY'], 0, 12) . '****'); ?></span>
        </div>
        <div class="config-item">
            <label>区域:</label>
            <span><?php 
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
        <button class="view-log-btn" data-instance-id="<?php echo htmlspecialchars($instance['INSTANCE_ID']); ?>">查看日志</button>
        <button class="edit-btn" data-index="<?php echo $index; ?>">编辑</button>
    </div>
</div>