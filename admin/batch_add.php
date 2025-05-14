<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $domains = array_filter(explode("\n", $_POST['domains']));
    $show_price = isset($_POST['show_price']);
    $show_notes = isset($_POST['show_notes']);
    $is_active = isset($_POST['is_active']);
    $default_price = floatval($_POST['default_price'] ?? 0);
    $default_notes = $_POST['default_notes'] ?? '';

    try {
        $stmt = $pdo->prepare("INSERT INTO domains (domain_name, price, show_price, notes, show_notes, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        
        foreach ($domains as $domain) {
            $domain = trim($domain);
            if (empty($domain)) continue;
            
            $stmt->execute([
                $domain,
                $default_price,
                $show_price ? 1 : 0,
                $default_notes,
                $show_notes ? 1 : 0,
                $is_active ? 1 : 0
            ]);
        }
        
        $success = true;
    } catch (Exception $e) {
        $error = '添加失败：' . $e->getMessage();
    }
}

$page_title = '批量添加域名';
$is_admin = true;
$base_path = '../';

include '../includes/header.php';
?>

<div class="container">
    <div class="header-section">
        <h1>批量添加域名</h1>
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

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>域名列表（每行一个）</label>
            <textarea name="domains" rows="10" required></textarea>
            <p class="help-text">每行输入一个域名</p>
        </div>

        <div class="form-group">
            <label>默认价格</label>
            <input type="number" name="default_price" step="0.01" value="0">
        </div>

        <div class="form-group">
            <label>默认备注</label>
            <textarea name="default_notes" rows="3"></textarea>
        </div>

        <div class="form-group">
            <label><input type="checkbox" name="show_price"> 显示价格</label>
        </div>

        <div class="form-group">
            <label><input type="checkbox" name="show_notes"> 显示备注</label>
        </div>

        <div class="form-group">
            <label><input type="checkbox" name="is_active" checked> 立即显示</label>
        </div>

        <button type="submit" class="btn">批量添加</button>
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