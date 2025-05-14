<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin'])) {
    die(json_encode(['success' => false, 'error' => '未登录']));
}

$id = filter_var($_GET['id'] ?? 0, FILTER_SANITIZE_NUMBER_INT);
if (!$id) {
    die(json_encode(['success' => false, 'error' => '无效的ID']));
}

try {
    $stmt = $pdo->prepare("SELECT * FROM domains WHERE id = ?");
    $stmt->execute([$id]);
    $domain = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($domain) {
        echo json_encode(['success' => true, 'domain' => $domain]);
    } else {
        echo json_encode(['success' => false, 'error' => '域名不存在']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 