<?php
$config = require 'config.php';

try {
    $pdo = new PDO("mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4", $config['db_user'], $config['db_pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // 获取系统设置
    try {
        $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
        $GLOBALS['settings'] = $stmt->fetch();
    } catch (PDOException $e) {
        // 如果获取设置失败，使用默认值
        $GLOBALS['settings'] = [
            'site_name' => '哈喽米表',
            'site_footer' => 'All Rights Reserved.',
        ];
    }
    
} catch (PDOException $e) {
    // 数据库连接错误处理
    die("数据库连接错误：" . $e->getMessage());
}
?> 