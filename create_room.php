<?php
require_once 'config/config.php';
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
$name = trim($_POST['name'] ?? '');
if ($name !== '') {
    $stmt = $pdo->prepare("INSERT INTO chat_rooms (name) VALUES (?)");
    $stmt->execute([$name]);
}
header('Location: chat.php');
exit; 