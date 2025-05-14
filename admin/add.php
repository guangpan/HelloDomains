<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$error = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 获取并过滤输入
    $domain_name = filter_var($_POST['domain_name'] ?? '', FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $price = $price === '' ? 0 : $price;
    $show_price = isset($_POST['show_price']) ? 1 : 0;
    $notes = filter_var($_POST['notes'] ?? '', FILTER_SANITIZE_STRING);
    $show_notes = isset($_POST['show_notes']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // 验证必填字段
    if (empty($domain_name)) {
        $error[] = '域名不能为空';
    }

    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO domains (domain_name, price, show_price, notes, show_notes, is_active) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$domain_name, $price, $show_price, $notes, $show_notes, $is_active]);
            $success = true;
        } catch (PDOException $e) {
            $error[] = '保存失败：' . $e->getMessage();
        }
    }
}

// 设置页面变量
$page_title = '添加域名';
$is_admin = true;
$base_path = '../';

include '../includes/header.php';
?>

<div class="container">
    <div class="header-section">
        <h1>添加域名</h1>
        <a href="index.php" class="btn">返回列表</a>
    </div>

    <!-- 操作按钮组，在移动端会变成下拉菜单 -->
    <div class="action-buttons" id="actionMenu">
        <!-- 移动端专用菜单项 -->
        <div class="mobile-menu-items">
            <hr class="menu-divider">
            <button class="menu-item theme-toggle-item" id="mobileThemeToggle">
                <span>深色模式</span>
                <div class="toggle-switch">
                    <span class="switch-handle"></span>
                </div>
            </button>
        </div>
    </div>

    <?php if ($success): ?>
        <div class="success">添加成功！</div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
        <div class="error">
            <?php foreach($error as $err): ?>
                <p><?php echo htmlspecialchars($err); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>域名</label>
            <input type="text" name="domain_name" required>
        </div>

        <div class="form-group">
            <label>价格</label>
            <input type="number" name="price" step="0.01">
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="show_price">
                    在前台显示价格
                </label>
            </div>
        </div>

        <div class="form-group">
            <label>备注</label>
            <textarea name="notes" rows="4"></textarea>
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="show_notes">
                    在前台显示备注
                </label>
            </div>
        </div>

        <div class="form-group checkbox-group">
            <label>
                <input type="checkbox" name="is_active" checked>
                立即启用
            </label>
        </div>

        <button type="submit" class="btn">保存</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const actionMenu = document.getElementById('actionMenu');
    const mobileThemeToggle = document.getElementById('mobileThemeToggle');
    
    // 菜单切换
    menuToggle?.addEventListener('click', function() {
        this.classList.toggle('active');
        actionMenu.classList.toggle('show');
    });

    // 点击其他地方关闭菜单
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.menu-toggle') && !e.target.closest('.action-buttons')) {
            menuToggle?.classList.remove('active');
            actionMenu.classList.remove('show');
        }
    });

    // 主题切换
    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
    }

    // 初始化主题状态
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);

    // 绑定主题切换事件
    mobileThemeToggle?.addEventListener('click', toggleTheme);
});
</script>

<?php include '../includes/footer.php'; ?> 