<?php
session_start();
require_once '../config/database.php';

// 如果已经登录，直接跳转到后台
if (isset($_SESSION['admin'])) {
    header('Location: index.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_var($_POST['username'] ?? '', FILTER_SANITIZE_STRING);
    $password = $_POST['password'] ?? '';
    $hcaptcha_response = $_POST['h-captcha-response'] ?? '';
    
    // 检查是否为本地开发环境
    $is_localhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);
    
    // 验证 hCaptcha（非本地环境才验证）
    $captcha_verified = false;
    if (!$is_localhost) {
        $hcaptcha_secret = "your-site-key"; //替换为你的 Site Key
        $verify_data = array(
            'secret' => $hcaptcha_secret,
            'response' => $hcaptcha_response
        );

        $verify_url = 'https://hcaptcha.com/siteverify';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $verify_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($verify_data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $response_data = json_decode($result);
        $captcha_verified = $response_data && $response_data->success;
    } else {
        // 本地环境直接通过验证
        $captcha_verified = true;
    }
    
    if (!$captcha_verified) {
        $error = '请完成人机验证';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->execute([$username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($admin && password_verify($password, $admin['password'])) {
                $_SESSION['admin'] = $admin['id'];
                header('Location: index.php');
                exit();
            } else {
                $error = '用户名或密码错误';
            }
        } catch (PDOException $e) {
            $error = '系统错误，请稍后再试';
        }
    }
}

// 设置页面变量
$page_title = '管理员登录';
$is_admin = true;
$base_path = '../';
$is_login_page = true;

include '../includes/header.php';
?>

<div class="login-container">
    <div class="header-section">
        <h2>管理员登录</h2>
        <!-- 移动端菜单按钮 -->
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>

    <!-- 操作按钮组 -->
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
    
    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>用户名</label>
            <input type="text" name="username" required>
        </div>
        
        <div class="form-group">
            <label>密码</label>
            <input type="password" name="password" required>
        </div>

        <?php if (!in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])): ?>
        <div class="form-group">
            <div class="h-captcha" data-sitekey="a024e944-8d08-469e-bd80-d232b57246e8"></div>
        </div>
        <?php endif; ?>
        
        <button type="submit" class="btn">登录</button>
    </form>
</div>

<?php if (!in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])): ?>
<!-- 添加 hCaptcha 脚本 -->
<script src="https://js.hcaptcha.com/1/api.js" async defer></script>
<?php endif; ?>

<?php
// 添加登录页面特定的 JavaScript
$extra_scripts = <<<EOT
<script>
document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.getElementById('menuToggle');
    const actionMenu = document.getElementById('actionMenu');
    const mobileThemeToggle = document.getElementById('mobileThemeToggle');
    
    // 菜单切换
    menuToggle?.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        this.classList.toggle('active');
        actionMenu.classList.toggle('show');
    });

    // 点击其他地方关闭菜单
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.menu-toggle') && !e.target.closest('.action-buttons')) {
            menuToggle?.classList.remove('active');
            actionMenu?.classList.remove('show');
        }
    });

    // 主题切换
    function updateThemeUI(theme) {
        if (mobileThemeToggle) {
            const switchHandle = mobileThemeToggle.querySelector('.switch-handle');
            if (theme === 'dark') {
                mobileThemeToggle.querySelector('.toggle-switch').style.backgroundColor = '#4CAF50';
                switchHandle.style.transform = 'translateX(20px)';
            } else {
                mobileThemeToggle.querySelector('.toggle-switch').style.backgroundColor = '';
                switchHandle.style.transform = 'translateX(0)';
            }
        }
    }

    function toggleTheme(e) {
        e.preventDefault();
        e.stopPropagation();
        const currentTheme = document.documentElement.getAttribute('data-theme');
        const newTheme = currentTheme === 'light' ? 'dark' : 'light';
        
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeUI(newTheme);
    }

    // 初始化主题状态
    const currentTheme = document.documentElement.getAttribute('data-theme');
    updateThemeUI(currentTheme);

    // 绑定主题切换事件
    mobileThemeToggle?.addEventListener('click', toggleTheme);
});
</script>
EOT;
?>

<?php include '../includes/footer.php'; ?> 