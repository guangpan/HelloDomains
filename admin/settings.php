<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

$page_title = '系统设置';
$is_admin = true;
$base_path = '../';

$error = '';
$success = false;

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $contact_info = trim($_POST['contact_info'] ?? '');
        $site_name = trim($_POST['site_name'] ?? '哈喽米表');
        $site_domain = trim($_POST['site_domain'] ?? '');
        $site_footer = trim($_POST['site_footer'] ?? 'All Rights Reserved.');
        $wechat = trim($_POST['wechat'] ?? '');
        $wechat_qr = trim($_POST['wechat_qr'] ?? '');
        $qq = trim($_POST['qq'] ?? '');
        $qq_qr = trim($_POST['qq_qr'] ?? '');
        $telegram = trim($_POST['telegram'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        $stmt = $pdo->prepare("UPDATE settings SET site_name = ?, site_domain = ?, site_footer = ?, contact_info = ?, wechat = ?, wechat_qr = ?, qq = ?, qq_qr = ?, telegram = ?, email = ? WHERE id = 1");
        $stmt->execute([$site_name, $site_domain, $site_footer, $contact_info, $wechat, $wechat_qr, $qq, $qq_qr, $telegram, $email]);

        // 处理 logo 上传
        if (!empty($_FILES['logo']['tmp_name'])) {
            $allowed_types = [
                'image/svg+xml' => 'svg',
                'image/png' => 'png',
                'image/jpeg' => 'jpg'
            ];
            
            if (isset($allowed_types[$_FILES['logo']['type']])) {
                $upload_dir = '../images/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // 删除旧的 logo 文件
                foreach (['svg', 'png', 'jpg'] as $ext) {
                    $old_file = $upload_dir . 'logo.' . $ext;
                    if (file_exists($old_file)) {
                        unlink($old_file);
                    }
                }
                
                $ext = $allowed_types[$_FILES['logo']['type']];
                $target_file = $upload_dir . 'logo.' . $ext;
                
                if ($ext === 'svg') {
                    // SVG 文件直接移动
                    move_uploaded_file($_FILES['logo']['tmp_name'], $target_file);
                } else {
                    // PNG/JPEG 需要处理尺寸
                    $image = null;
                    if ($ext === 'png') {
                        $image = imagecreatefrompng($_FILES['logo']['tmp_name']);
                    } else {
                        $image = imagecreatefromjpeg($_FILES['logo']['tmp_name']);
                    }
                    
                    // 调整图片大小
                    $width = imagesx($image);
                    $height = imagesy($image);
                    if ($height > 60) {
                        $new_width = floor($width * (60 / $height));
                        $new_height = 60;
                        $new_image = imagecreatetruecolor($new_width, $new_height);
                        
                        // 保持透明度
                        if ($ext === 'png') {
                            imagealphablending($new_image, false);
                            imagesavealpha($new_image, true);
                        }
                        
                        imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                        
                        if ($ext === 'png') {
                            imagepng($new_image, $target_file);
                        } else {
                            imagejpeg($new_image, $target_file, 90);
                        }
                        imagedestroy($new_image);
                    } else {
                        if ($ext === 'png') {
                            imagepng($image, $target_file);
                        } else {
                            imagejpeg($image, $target_file, 90);
                        }
                    }
                    imagedestroy($image);
                }
            }
        }

        $success = true;
    }
    
    // 获取当前设置
    $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $error = '操作失败：' . $e->getMessage();
}

// 包含头部
include '../includes/header.php';
?>

<div class="container">
    <div class="header-section">
        <h1>系统设置</h1>
        <a href="index.php" class="btn">返回列表</a>
    </div>

    <?php if ($success): ?>
        <div class="success">保存成功！</div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" action="" enctype="multipart/form-data">
        <div class="form-group">
            <label>网站名称</label>
            <input type="text" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? '哈喽米表'); ?>" required>
            <p class="help-text">网站名称将显示在浏览器标题栏和页面顶部</p>
        </div>

        <div class="form-group">
            <label>网站域名</label>
            <input type="text" name="site_domain" value="<?php echo htmlspecialchars($settings['site_domain'] ?? ''); ?>" placeholder="例如：example.com">
            <p class="help-text">设置网站域名（不需要包含http://或https://），用于页脚网站名称的超链接</p>
        </div>

        <div class="form-group">
            <label>页脚信息</label>
            <input type="text" name="site_footer" value="<?php echo htmlspecialchars($settings['site_footer'] ?? 'All Rights Reserved.'); ?>">
            <p class="help-text">页脚显示的版权或其他信息（年份会自动添加）</p>
        </div>

        <div class="form-group">
            <label>Logo</label>
            <?php if (file_exists('../images/logo.svg')): ?>
            <div class="current-logo">
                <img src="../images/logo.svg" alt="Current Logo" style="max-height: 60px; margin: 10px 0;">
            </div>
            <?php elseif (file_exists('../images/logo.png')): ?>
            <div class="current-logo">
                <img src="../images/logo.png" alt="Current Logo" style="max-height: 60px; margin: 10px 0;">
            </div>
            <?php endif; ?>
            <input type="file" name="logo" accept="image/svg+xml,image/png,image/jpeg">
            <p class="help-text">建议上传 SVG 或透明背景的 PNG 图片，高度不超过 60 像素</p>
        </div>

        <div class="form-group">
            <label>联系方式说明</label>
            <textarea name="contact_info" rows="3"><?php echo htmlspecialchars($settings['contact_info'] ?? ''); ?></textarea>
            <p class="help-text">此说明文字将显示在首页联系方式上方</p>
        </div>

        <div class="contact-settings">
            <div class="form-group">
                <label>微信号</label>
                <input type="text" name="wechat" value="<?php echo htmlspecialchars($settings['wechat'] ?? ''); ?>">
                <div class="qr-upload">
                    <label>微信二维码图片地址</label>
                    <div class="url-input">
                        <input type="url" 
                               name="wechat_qr" 
                               value="<?php echo htmlspecialchars($settings['wechat_qr'] ?? ''); ?>" 
                               placeholder="请输入图片URL，例如：http://example.com/images/wechat.png">
                    </div>
                    <?php if (!empty($settings['wechat_qr'])): ?>
                    <div class="current-qr">
                        <img src="<?php echo htmlspecialchars($settings['wechat_qr']); ?>" alt="WeChat QR">
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label>QQ号</label>
                <input type="text" name="qq" value="<?php echo htmlspecialchars($settings['qq'] ?? ''); ?>">
                <div class="qr-upload">
                    <label>QQ二维码图片地址</label>
                    <div class="url-input">
                        <input type="url" 
                               name="qq_qr" 
                               value="<?php echo htmlspecialchars($settings['qq_qr'] ?? ''); ?>" 
                               placeholder="请输入图片URL，例如：http://example.com/images/qq.png">
                    </div>
                    <?php if (!empty($settings['qq_qr'])): ?>
                    <div class="current-qr">
                        <img src="<?php echo htmlspecialchars($settings['qq_qr']); ?>" alt="QQ QR">
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label>Telegram</label>
                <input type="text" name="telegram" value="<?php echo htmlspecialchars($settings['telegram'] ?? ''); ?>">
                <p class="help-text">请输入完整的Telegram用户名，例如：@username</p>
            </div>

            <div class="form-group">
                <label>邮箱</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($settings['email'] ?? ''); ?>">
            </div>
        </div>

        <button type="submit" class="btn">保存设置</button>
    </form>
</div>

<?php
// 包含底部
include '../includes/footer.php';
?> 