<?php
require_once 'config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if mission_id is provided
if (!isset($_POST['mission_id'])) {
    $_SESSION['error'] = 'Invalid mission request.';
    header('Location: missions.php');
    exit;
}

$mission_id = (int)$_POST['mission_id'];

try {
    // Check if mission exists and is available
    $stmt = $pdo->prepare("
        SELECT m.*, 
               CASE WHEN um.id IS NOT NULL THEN um.status ELSE 'not_started' END as user_status
        FROM missions m
        LEFT JOIN user_missions um ON m.id = um.mission_id AND um.user_id = ?
        WHERE m.id = ?
    ");
    $stmt->execute([$_SESSION['user_id'], $mission_id]);
    $mission = $stmt->fetch();

    if (!$mission) {
        $_SESSION['error'] = 'Mission not found.';
        header('Location: missions.php');
        exit;
    }

    if ($mission['user_status'] !== 'not_started') {
        $_SESSION['error'] = 'You have already started or completed this mission.';
        header('Location: missions.php');
        exit;
    }

    // Start the mission
    $stmt = $pdo->prepare("
        INSERT INTO user_missions (user_id, mission_id, status, started_at)
        VALUES (?, ?, 'in_progress', NOW())
    ");
    $stmt->execute([$_SESSION['user_id'], $mission_id]);

    $_SESSION['success'] = 'Mission started successfully!';
    header('Location: missions.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = 'An error occurred while starting the mission.';
    header('Location: missions.php');
    exit;
} 