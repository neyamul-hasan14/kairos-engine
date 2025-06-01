<?php
require_once 'config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');
    $timeline_tag = trim($_POST['timeline_tag'] ?? '');

    if (empty($content)) {
        $_SESSION['error'] = 'Post content cannot be empty.';
        header('Location: dashboard.php');
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO posts (user_id, content, timeline_tag)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$_SESSION['user_id'], $content, $timeline_tag]);

        $_SESSION['success'] = 'Post created successfully!';
    } catch (PDOException $e) {
        $_SESSION['error'] = 'Failed to create post. Please try again.';
    }

    header('Location: dashboard.php');
    exit;
} else {
    header('Location: dashboard.php');
    exit;
} 