<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$id = filter_var($_GET['id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    header('Location: index.php');
    exit();
}

$error = [];
$success = false;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // 获取并过滤输入
        $domain_name = filter_var($_POST['domain_name'] ?? '', FILTER_SANITIZE_STRING);
        $price = filter_var($_POST['price'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        $show_price = isset($_POST['show_price']) ? 1 : 0;
        $notes = filter_var($_POST['notes'] ?? '', FILTER_SANITIZE_STRING);
        $show_notes = isset($_POST['show_notes']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        // 验证必填字段
        if (empty($domain_name)) {
            $error[] = '域名不能为空';
        }

        if (empty($error)) {
            $stmt = $pdo->prepare("UPDATE domains SET domain_name = ?, price = ?, show_price = ?, notes = ?, show_notes = ?, is_active = ? WHERE id = ?");
            $stmt->execute([$domain_name, $price, $show_price, $notes, $show_notes, $is_active, $id]);
            $success = true;
        }
    }

    // 获取当前域名信息
    $stmt = $pdo->prepare("SELECT * FROM domains WHERE id = ?");
    $stmt->execute([$id]);
    $domain = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$domain) {
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    $error[] = '操作失败：' . $e->getMessage();
}

// 设置页面变量
$page_title = '编辑域名';
$is_admin = true;
$base_path = '../';

include '../includes/header.php';
?>

<div class="container">
    <div class="header-section">
        <h1>编辑域名</h1>
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>

    <div class="action-buttons" id="actionMenu">
        <a href="index.php" class="btn">返回列表</a>
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
        <div class="success">保存成功！</div>
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
            <input type="text" name="domain_name" value="<?php echo htmlspecialchars($domain['domain_name']); ?>" required>
        </div>

        <div class="form-group">
            <label>价格</label>
            <input type="number" name="price" step="0.01" value="<?php echo htmlspecialchars($domain['price']); ?>">
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="show_price" <?php echo $domain['show_price'] ? 'checked' : ''; ?>>
                    在前台显示价格
                </label>
            </div>
        </div>

        <div class="form-group">
            <label>备注</label>
            <textarea name="notes" rows="4"><?php echo htmlspecialchars($domain['notes']); ?></textarea>
            <div class="checkbox-group">
                <label>
                    <input type="checkbox" name="show_notes" <?php echo $domain['show_notes'] ? 'checked' : ''; ?>>
                    在前台显示备注
                </label>
            </div>
        </div>

        <div class="form-group checkbox-group">
            <label>
                <input type="checkbox" name="is_active" <?php echo $domain['is_active'] ? 'checked' : ''; ?>>
                启用
            </label>
        </div>

        <button type="submit" class="btn">保存</button>
    </form>
</div>

<?php include '../includes/footer.php'; ?> 