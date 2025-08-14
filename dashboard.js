// dashboard.js - 前端交互脚本
document.addEventListener('DOMContentLoaded', function() {
    // 新增配置面板控制
    const newConfigBtn = document.getElementById('newConfigBtn');
    const newConfigPanel = document.getElementById('newConfigPanel');
    const togglePanelBtn = document.getElementById('togglePanelBtn');
    const panelContent = document.querySelector('.panel-content');
    
    // 初始状态：面板折叠
    panelContent.style.display = 'none';
    
    if (togglePanelBtn) {
        togglePanelBtn.addEventListener('click', function() {
            if (panelContent.style.display === 'none') {
                panelContent.style.display = 'block';
                togglePanelBtn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M7.41,15.41L12,10.83L16.59,15.41L18,14L12,8L6,14L7.41,15.41Z"/></svg>';
            } else {
                panelContent.style.display = 'none';
                togglePanelBtn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M7.41,8.58L12,13.17L16.59,8.58L18,10L12,16L6,10L7.41,8.58Z"/></svg>';
            }
        });
    }
    
    if (newConfigBtn) {
        newConfigBtn.addEventListener('click', function() {
            panelContent.style.display = 'block';
            togglePanelBtn.innerHTML = '<svg viewBox="0 0 24 24"><path d="M7.41,15.41L12,10.83L16.59,15.41L18,14L12,8L6,14L7.41,15.41Z"/></svg>';
        });
    }
    
    // 表单提交处理
    const newConfigForm = document.getElementById('newConfigForm');
    if (newConfigForm) {
        newConfigForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('save_config.php?action=create', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('配置添加成功！');
                    location.reload();
                } else {
                    alert('添加失败: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('添加失败: 网络错误');
            });
        });
    }
    
    // 日志查看
    const logModal = document.getElementById('logModal');
    const logContent = document.getElementById('logContent');
    const logInstanceId = document.getElementById('logInstanceId');
    const closeModal = document.querySelector('.close');
    const viewLogBtns = document.querySelectorAll('.view-log-btn');
    
    if (viewLogBtns.length) {
        viewLogBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const instanceId = this.getAttribute('data-instance-id');
                logContent.textContent = '正在加载日志...';
                logInstanceId.textContent = `${instanceId} 的日志`;
                logModal.style.display = 'flex';
                
                fetch('get_log.php?instanceId=' + encodeURIComponent(instanceId))
                    .then(response => response.text())
                    .then(data => {
                        logContent.textContent = data || '暂无日志';
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        logContent.textContent = '加载日志失败';
                    });
            });
        });
    }
    
    if (closeModal) {
        closeModal.addEventListener('click', function() {
            logModal.style.display = 'none';
        });
    }
    
    window.addEventListener('click', function(e) {
        if (e.target === logModal) {
            logModal.style.display = 'none';
        }
    });
    
    // 编辑按钮处理
    const editModal = document.getElementById('editModal');
    const editForm = document.getElementById('editConfigForm');
    const closeEditBtns = document.querySelectorAll('.close-edit');
    const editBtns = document.querySelectorAll('.edit-btn');
    
    if (editBtns.length) {
        editBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const index = this.getAttribute('data-index');
                const instance = allInstances[index];
                
                if (instance) {
                    document.getElementById('editOriginalIndex').value = index;
                    document.getElementById('editKeyId').value = instance.AWS_ACCESS_KEY_ID;
                    document.getElementById('editAccessKey').value = instance.AWS_SECRET_ACCESS_KEY;
                    document.getElementById('editInstanceId').value = instance.INSTANCE_ID;
                    document.getElementById('editRegion').value = instance.AWS_DEFAULT_REGION;
                    document.getElementById('editType').value = instance.INSTANCE_TYPE;
                    editModal.style.display = 'flex';
                }
            });
        });
    }
    
    // 关闭编辑模态框
    if (closeEditBtns.length) {
        closeEditBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                editModal.style.display = 'none';
            });
        });
    }
    
    window.addEventListener('click', function(e) {
        if (e.target === editModal) {
            editModal.style.display = 'none';
        }
    });

    // 编辑表单提交
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const index = formData.get('original_index');
            
            fetch('save_config.php?action=update&index=' + index, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('配置更新成功！');
                    location.reload();
                } else {
                    alert('更新失败: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('更新失败: 网络错误');
            });
        });
    }

    // 删除按钮事件
    const deleteBtns = document.querySelectorAll('.delete-btn');
    if (deleteBtns.length) {
        deleteBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const index = this.getAttribute('data-index');
                const instanceId = this.getAttribute('data-instance-id');
                
                if (confirm(`确定要删除配置 "${instanceId}" 吗？此操作不可撤销。`)) {
                    fetch('save_config.php?action=delete&index=' + index, {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('配置已删除！');
                            location.reload();
                        } else {
                            alert('删除失败: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('删除失败: 网络错误');
                    });
                }
            });
        });
    }
    
    // 搜索功能
    const searchEC2 = document.getElementById('searchEC2');
    if (searchEC2) {
        searchEC2.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.instance-section:first-child .config-card');
            
            cards.forEach(card => {
                const title = card.querySelector('.card-header h3').textContent.toLowerCase();
                if (title.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    const searchLightsail = document.getElementById('searchLightsail');
    if (searchLightsail) {
        searchLightsail.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.instance-section:last-child .config-card');
            
            cards.forEach(card => {
                const title = card.querySelector('.card-header h3').textContent.toLowerCase();
                if (title.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});