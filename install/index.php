<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 检查是否已安装
if (file_exists('../config/config.php')) {
    // 尝试连接数据库
    try {
        require_once '../config/config.php';
        $config = require '../config/config.php';
        $pdo = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']}", 
            $config['db_user'], 
            $config['db_pass']
        );
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // 检查admins表是否存在且有数据
        $stmt = $pdo->query("SELECT COUNT(*) FROM admins");
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            // 已经安装完成
            header('Location: ../index.php');
            exit('系统已安装，如需重新安装请删除 config/config.php 文件');
        }
    } catch (PDOException $e) {
        // 数据库连接失败，继续安装流程
    }
}

// 生成随机token防止CSRF攻击
if (empty($_SESSION['install_token'])) {
    $_SESSION['install_token'] = bin2hex(random_bytes(32));
}

$error = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 验证CSRF token
    if (!hash_equals($_SESSION['install_token'], $_POST['token'] ?? '')) {
        die('无效的请求');
    }

    // 验证并过滤输入
    $db_host = filter_var($_POST['db_host'] ?? '', FILTER_SANITIZE_STRING);
    $db_name = filter_var($_POST['db_name'] ?? '', FILTER_SANITIZE_STRING);
    $db_user = filter_var($_POST['db_user'] ?? '', FILTER_SANITIZE_STRING);
    $db_pass = $_POST['db_pass'] ?? '';
    $admin_user = filter_var($_POST['admin_user'] ?? '', FILTER_SANITIZE_STRING);
    $admin_pass = $_POST['admin_pass'] ?? '';
    $hcaptcha_site_key = filter_var($_POST['hcaptcha_site_key'] ?? '', FILTER_SANITIZE_STRING);
    $hcaptcha_secret_key = filter_var($_POST['hcaptcha_secret_key'] ?? '', FILTER_SANITIZE_STRING);
    
    // 验证必填字段
    if (empty($db_host) || empty($db_name) || empty($db_user) || 
        empty($admin_user) || empty($admin_pass)) {
        $error[] = '所有字段都必须填写';
    }
    
    if (empty($error)) {
        try {
            // 测试数据库连接
            $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->exec("set names utf8mb4");
            
            // 创建数据库
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . $db_name . "` 
                       CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `" . $db_name . "`");
            
            // 创建域名表
            $pdo->exec("CREATE TABLE IF NOT EXISTS domains (
                id INT AUTO_INCREMENT PRIMARY KEY,
                domain_name VARCHAR(255) NOT NULL,
                price DECIMAL(10,2),
                show_price BOOLEAN DEFAULT false,
                notes TEXT,
                show_notes BOOLEAN DEFAULT false,
                is_active BOOLEAN DEFAULT true,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // 创建设置表
            $pdo->exec("CREATE TABLE IF NOT EXISTS settings (
                id INT AUTO_INCREMENT PRIMARY KEY,
                site_name VARCHAR(255) DEFAULT '哈喽米表',
                site_domain VARCHAR(255) DEFAULT '',
                site_footer VARCHAR(255) DEFAULT 'All Rights Reserved.',
                contact_info TEXT,
                wechat VARCHAR(255),
                wechat_qr VARCHAR(255),
                qq VARCHAR(255),
                qq_qr VARCHAR(255),
                telegram VARCHAR(255),
                email VARCHAR(255),
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )");
            
            // 初始化设置表
            $pdo->exec("INSERT INTO settings (site_name, site_domain, site_footer, contact_info, wechat, wechat_qr, qq, qq_qr, telegram, email) 
                        VALUES ('哈喽米表', '', 'All Rights Reserved.', '', '', '', '', '', '', '')");
            
            // 创建管理员表
            $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
                id INT AUTO_INCREMENT PRIMARY KEY,
                username VARCHAR(50) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            // 添加管理员账号
            $admin_pass_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
            $stmt->execute([$admin_user, $admin_pass_hash]);
            
            // 创建配置文件
            $config_content = "<?php
return [
    'db_host' => '" . addslashes($db_host) . "',
    'db_name' => '" . addslashes($db_name) . "',
    'db_user' => '" . addslashes($db_user) . "',
    'db_pass' => '" . addslashes($db_pass) . "',
    
    // hCaptcha 配置
    'hcaptcha_site_key' => '" . addslashes($hcaptcha_site_key) . "',
    'hcaptcha_secret_key' => '" . addslashes($hcaptcha_secret_key) . "'
];";
            
            // 创建配置目录
            if (!file_exists('../config')) {
                mkdir('../config', 0755, true);
            }
            
            // 写入配置文件
            file_put_contents('../config/config.php', $config_content);
            chmod('../config/config.php', 0644);
            
            $success = true;
            
        } catch (PDOException $e) {
            $error[] = '数据库错误：' . $e->getMessage();
        } catch (Exception $e) {
            $error[] = '安装错误：' . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <title>哈喽米表安装</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background: #f8f9fa;
            color: #333;
        }
        .container {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-weight: 600;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            margin-top: 0;
        }
        h2 {
            color: #3498db;
            font-size: 20px;
            margin-top: 25px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #444;
        }
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            transition: border-color 0.3s;
            font-size: 14px;
        }
        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #3498db;
            outline: none;
            box-shadow: 0 0 5px rgba(52, 152, 219, 0.3);
        }
        .error {
            color: #e74c3c;
            background: #fff5f5;
            padding: 12px;
            border-left: 4px solid #e74c3c;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success {
            color: #27ae60;
            background: #f0fff4;
            padding: 15px;
            border-left: 4px solid #27ae60;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .success a {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
        }
        .success a:hover {
            text-decoration: underline;
        }
        .btn {
            background: #3498db;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            display: block;
            width: 100%;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #2980b9;
        }
        .section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 6px;
            margin-bottom: 25px;
        }
        .optional {
            color: #7f8c8d;
            font-size: 12px;
            margin-left: 8px;
            font-weight: normal;
        }
        .hint {
            font-size: 12px;
            color: #7f8c8d;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>哈喽米表安装</h1>
        
        <?php if ($success): ?>
            <div class="success">
                <p><strong>安装成功！</strong></p>
                <p>系统已经成功安装完成。为了安全起见，请删除install目录。</p>
                <p>现在您可以 <a href="../admin/login.php">登录后台</a> 开始管理您的域名。</p>
            </div>
        <?php else: ?>
            <?php if (!empty($error)): ?>
                <div class="error">
                    <?php foreach($error as $err): ?>
                        <p><?php echo htmlspecialchars($err); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="token" value="<?php echo $_SESSION['install_token']; ?>">
                
                <div class="section">
                    <h2>数据库配置</h2>
                    <div class="form-group">
                        <label>数据库主机</label>
                        <input type="text" name="db_host" value="localhost" required>
                        <div class="hint">通常为 "localhost" 或数据库服务器IP</div>
                    </div>
                    
                    <div class="form-group">
                        <label>数据库名</label>
                        <input type="text" name="db_name" value="" required>
                        <div class="hint">输入要使用的数据库名称，如不存在将会自动创建</div>
                    </div>
                    
                    <div class="form-group">
                        <label>数据库用户名</label>
                        <input type="text" name="db_user" required>
                    </div>
                    
                    <div class="form-group">
                        <label>数据库密码</label>
                        <input type="password" name="db_pass">
                    </div>
                </div>
                
                <div class="section">
                    <h2>管理员账号</h2>
                    <div class="form-group">
                        <label>管理员用户名</label>
                        <input type="text" name="admin_user" required>
                    </div>
                    
                    <div class="form-group">
                        <label>管理员密码</label>
                        <input type="password" name="admin_pass" required>
                    </div>
                </div>
                
                <div class="section">
                    <h2>hCaptcha配置 <span class="optional">(可选)</span></h2>
                    <div class="form-group">
                        <label>Site Key</label>
                        <input type="text" name="hcaptcha_site_key" placeholder="留空表示不使用hCaptcha">
                    </div>
                    
                    <div class="form-group">
                        <label>Secret Key</label>
                        <input type="text" name="hcaptcha_secret_key" placeholder="留空表示不使用hCaptcha">
                        <div class="hint">如果您不需要验证码功能，可以保留为空</div>
                    </div>
                </div>
                
                <button type="submit" class="btn">开始安装</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html> 