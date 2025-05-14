<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit();
}

$action = $_POST['action'] ?? '';
$selected = $_POST['selected'] ?? [];

if (empty($action) || empty($selected)) {
    $_SESSION['error'] = '请选择要操作的域名和操作类型';
    header('Location: index.php');
    exit();
}

try {
    switch ($action) {
        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM domains WHERE id IN (" . str_repeat('?,', count($selected) - 1) . "?)");
            $stmt->execute($selected);
            $_SESSION['success'] = '成功删除选中的域名';
            break;
            
        case 'show':
            $stmt = $pdo->prepare("UPDATE domains SET is_active = 1 WHERE id IN (" . str_repeat('?,', count($selected) - 1) . "?)");
            $stmt->execute($selected);
            $_SESSION['success'] = '成功显示选中的域名';
            break;
            
        case 'hide':
            $stmt = $pdo->prepare("UPDATE domains SET is_active = 0 WHERE id IN (" . str_repeat('?,', count($selected) - 1) . "?)");
            $stmt->execute($selected);
            $_SESSION['success'] = '成功隐藏选中的域名';
            break;

        case 'show_price':
            $stmt = $pdo->prepare("UPDATE domains SET show_price = 1 WHERE id IN (" . str_repeat('?,', count($selected) - 1) . "?)");
            $stmt->execute($selected);
            $_SESSION['success'] = '成功设置显示选中域名的价格';
            break;

        case 'hide_price':
            $stmt = $pdo->prepare("UPDATE domains SET show_price = 0 WHERE id IN (" . str_repeat('?,', count($selected) - 1) . "?)");
            $stmt->execute($selected);
            $_SESSION['success'] = '成功设置隐藏选中域名的价格';
            break;

        case 'show_notes':
            $stmt = $pdo->prepare("UPDATE domains SET show_notes = 1 WHERE id IN (" . str_repeat('?,', count($selected) - 1) . "?)");
            $stmt->execute($selected);
            $_SESSION['success'] = '成功设置显示选中域名的备注';
            break;

        case 'hide_notes':
            $stmt = $pdo->prepare("UPDATE domains SET show_notes = 0 WHERE id IN (" . str_repeat('?,', count($selected) - 1) . "?)");
            $stmt->execute($selected);
            $_SESSION['success'] = '成功设置隐藏选中域名的备注';
            break;
            
        default:
            $_SESSION['error'] = '未知的操作类型';
    }
} catch (Exception $e) {
    $_SESSION['error'] = '操作失败：' . $e->getMessage();
}

header('Location: index.php'); 