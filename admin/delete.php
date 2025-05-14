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

try {
    $stmt = $pdo->prepare("DELETE FROM domains WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: index.php');
} catch (PDOException $e) {
    die('删除失败：' . $e->getMessage());
} 