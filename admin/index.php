<?php
session_start();
require_once '../config/database.php';

// 如果已经登录，直接跳转到后台
if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

try {
    if (!isset($pdo)) {
        throw new Exception("数据库连接失败");
    }
    $stmt = $pdo->query("SELECT * FROM domains ORDER BY created_at DESC");
    $domains = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("数据库连接错误：" . $e->getMessage());
}

// 设置页面变量
$page_title = '域名管理后台';
$is_admin = true;
$base_path = '../';

// 包含头部
include '../includes/header.php';
?>

<div class="container">
    <div class="header-section">
        <h1>域名管理</h1>
        <!-- 移动端菜单按钮 -->
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>

    <!-- 操作按钮组，在移动端会变成下拉菜单 -->
    <div class="action-buttons" id="actionMenu">
        <a href="add.php" class="btn">添加新域名</a>
        <a href="batch_add.php" class="btn">批量添加</a>
        <a href="settings.php" class="btn">系统设置</a>
        <!-- 移动端专用菜单项 -->
        <div class="mobile-menu-items">
            <hr class="menu-divider">
            <button class="menu-item theme-toggle-item" id="mobileThemeToggle">
                <span>深色模式</span>
                <div class="toggle-switch">
                    <span class="switch-handle"></span>
                </div>
            </button>
            <hr class="menu-divider">
            <div class="menu-item">
                <select name="action" id="batchActionMobile" class="mobile-select">
                    <option value="">批量操作</option>
                    <option value="delete">删除</option>
                    <option value="show">显示域名</option>
                    <option value="hide">隐藏域名</option>
                    <option value="show_price">显示价格</option>
                    <option value="hide_price">隐藏价格</option>
                    <option value="show_notes">显示备注</option>
                    <option value="hide_notes">隐藏备注</option>
                </select>
                <button type="button" class="btn btn-batch" id="batchButtonMobile" disabled>应用</button>
            </div>
        </div>
    </div>
    
    <form id="batchForm" method="POST" action="batch_action.php">
        <!-- 桌面端表格视图 -->
        <div class="batch-actions">
            <select name="action" id="batchAction">
                <option value="">批量操作</option>
                <option value="delete">删除</option>
                <option value="show">显示域名</option>
                <option value="hide">隐藏域名</option>
                <option value="show_price">显示价格</option>
                <option value="hide_price">隐藏价格</option>
                <option value="show_notes">显示备注</option>
                <option value="hide_notes">隐藏备注</option>
            </select>
            <button type="submit" class="btn btn-batch" id="batchButtonDesktop" disabled>应用</button>
        </div>

        <table class="domain-table">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" id="selectAll">
                    </th>
                    <th>域名</th>
                    <th>价格</th>
                    <th>显示价格</th>
                    <th>显示备注</th>
                    <th>状态</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($domains as $domain): ?>
                <tr>
                    <td>
                        <input type="checkbox" name="selected[]" value="<?php echo $domain['id']; ?>">
                    </td>
                    <td><?php echo htmlspecialchars($domain['domain_name']); ?></td>
                    <td><?php echo number_format($domain['price'], 2); ?></td>
                    <td><?php echo $domain['show_price'] ? '是' : '否'; ?></td>
                    <td><?php echo $domain['show_notes'] ? '是' : '否'; ?></td>
                    <td><?php echo $domain['is_active'] ? '显示' : '隐藏'; ?></td>
                    <td>
                        <button onclick="openEditModal(<?php echo $domain['id']; ?>)" class="btn-edit">编辑</button>
                        <a href="delete.php?id=<?php echo $domain['id']; ?>" class="btn-delete" onclick="return confirm('确定要删除吗？')">删除</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- 移动端卡片视图 -->
        <div class="mobile-domain-list">
            <?php foreach($domains as $domain): ?>
            <div class="domain-card">
                <div class="domain-card-header">
                    <label class="checkbox-wrapper">
                        <input type="checkbox" name="selected[]" value="<?php echo $domain['id']; ?>">
                        <span class="domain-card-name"><?php echo htmlspecialchars($domain['domain_name']); ?></span>
                    </label>
                </div>
                <div class="domain-card-content">
                    <div class="domain-card-item">
                        <span class="domain-card-label">价格:</span>
                        <span><?php echo number_format($domain['price'], 2); ?></span>
                    </div>
                    <div class="domain-card-item">
                        <span class="domain-card-label">显示价格:</span>
                        <span><?php echo $domain['show_price'] ? '是' : '否'; ?></span>
                    </div>
                    <div class="domain-card-item">
                        <span class="domain-card-label">显示备注:</span>
                        <span><?php echo $domain['show_notes'] ? '是' : '否'; ?></span>
                    </div>
                    <div class="domain-card-item">
                        <span class="domain-card-label">状态:</span>
                        <span><?php echo $domain['is_active'] ? '显示' : '隐藏'; ?></span>
                    </div>
                </div>
                <div class="domain-card-actions">
                    <button onclick="openEditModal(<?php echo $domain['id']; ?>)" class="btn-edit">编辑</button>
                    <a href="delete.php?id=<?php echo $domain['id']; ?>" class="btn-delete" onclick="return confirm('确定要删除吗？')">删除</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </form>
</div>

<!-- 在页面底部添加模态框 HTML -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>编辑域名</h2>
            <button class="close-modal" onclick="closeEditModal()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editForm" method="POST">
                <input type="hidden" name="domain_id" id="domain_id">
                <div class="form-group">
                    <label>域名</label>
                    <input type="text" name="domain_name" id="domain_name" required>
                </div>

                <div class="form-group">
                    <label>价格</label>
                    <input type="number" name="price" id="price" step="0.01">
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="show_price" id="show_price">
                            在前台显示价格
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>备注</label>
                    <textarea name="notes" id="notes" rows="4"></textarea>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="show_notes" id="show_notes">
                            在前台显示备注
                        </label>
                    </div>
                </div>

                <div class="form-group checkbox-group">
                    <label>
                        <input type="checkbox" name="is_active" id="is_active">
                        启用
                    </label>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-cancel" onclick="closeEditModal()">取消</button>
                    <button type="submit" class="btn btn-primary">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 添加 JavaScript -->
<?php
$extra_scripts = <<<EOT
<script>
async function openEditModal(id) {
    try {
        const response = await fetch('get_domain.php?id=' + id);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('domain_id').value = data.domain.id;
            document.getElementById('domain_name').value = data.domain.domain_name;
            document.getElementById('price').value = data.domain.price;
            document.getElementById('show_price').checked = data.domain.show_price == 1;
            document.getElementById('notes').value = data.domain.notes;
            document.getElementById('show_notes').checked = data.domain.show_notes == 1;
            document.getElementById('is_active').checked = data.domain.is_active == 1;
            
            document.getElementById('editModal').classList.add('show');
            document.body.style.overflow = 'hidden';
        } else {
            alert('加载数据失败');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('加载数据失败');
    }
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('show');
    document.body.style.overflow = '';
}

document.getElementById('editForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    try {
        const formData = new FormData(this);
        const response = await fetch('update_domain.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            closeEditModal();
            location.reload();
        } else {
            alert(result.error || '保存失败');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('保存失败');
    }
});

// 点击模态框外部关闭
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeEditModal();
    }
});
</script>
EOT;
?>

<?php include '../includes/footer.php'; ?> 