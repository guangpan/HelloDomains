<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin'])) {
    die(json_encode(['success' => false, 'error' => '未登录']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'error' => '无效的请求方法']));
}

try {
    $id = filter_var($_POST['domain_id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
    $domain_name = filter_var($_POST['domain_name'] ?? '', FILTER_SANITIZE_STRING);
    $price = filter_var($_POST['price'] ?? '', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $show_price = isset($_POST['show_price']) ? 1 : 0;
    $notes = filter_var($_POST['notes'] ?? '', FILTER_SANITIZE_STRING);
    $show_notes = isset($_POST['show_notes']) ? 1 : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (empty($domain_name)) {
        die(json_encode(['success' => false, 'error' => '域名不能为空']));
    }

    $stmt = $pdo->prepare("UPDATE domains SET domain_name = ?, price = ?, show_price = ?, notes = ?, show_notes = ?, is_active = ? WHERE id = ?");
    $stmt->execute([$domain_name, $price, $show_price, $notes, $show_notes, $is_active, $id]);
    
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 