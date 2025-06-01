<?php
require_once 'config/config.php';
session_start();
if (!isset($_SESSION['user_id'])) exit;
$room_id = isset($_POST['room_id']) ? (int)$_POST['room_id'] : 0;
$message = trim($_POST['message'] ?? '');
if ($room_id && $message !== '') {
    $stmt = $pdo->prepare("INSERT INTO chat_messages (room_id, user_id, message) VALUES (?, ?, ?)");
    $stmt->execute([$room_id, $_SESSION['user_id'], htmlspecialchars($message)]);
} 