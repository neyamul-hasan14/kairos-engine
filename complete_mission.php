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
    // Start transaction
    $pdo->beginTransaction();

    // Check if mission exists and is in progress
    $stmt = $pdo->prepare("
        SELECT m.*, um.id as user_mission_id
        FROM missions m
        JOIN user_missions um ON m.id = um.mission_id
        WHERE m.id = ? AND um.user_id = ? AND um.status = 'in_progress'
    ");
    $stmt->execute([$mission_id, $_SESSION['user_id']]);
    $mission = $stmt->fetch();

    if (!$mission) {
        $_SESSION['error'] = 'Mission not found or not in progress.';
        header('Location: missions.php');
        exit;
    }

    // Update mission status to completed
    $stmt = $pdo->prepare("
        UPDATE user_missions 
        SET status = 'completed', completed_at = NOW()
        WHERE id = ?
    ");
    $stmt->execute([$mission['user_mission_id']]);

    // Add points to user's account
    $stmt = $pdo->prepare("
        UPDATE users 
        SET points = points + ?
        WHERE id = ?
    ");
    $stmt->execute([$mission['points_reward'], $_SESSION['user_id']]);

    // Check and award badges
    $earned_badges = []; // Array to store newly earned badges

    // 1. Mission Master Badge (10 completed missions)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as completed_count
        FROM user_missions
        WHERE user_id = ? AND status = 'completed'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $completed_count = $stmt->fetch()['completed_count'];
    
    if ($completed_count >= 10) {
        // Check if user already has the badge
        $stmt = $pdo->prepare("
            SELECT 1 FROM user_badges ub
            JOIN badges b ON ub.badge_id = b.id
            WHERE ub.user_id = ? AND b.name = 'Mission Master'
        ");
        $stmt->execute([$_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            // Award the badge
            $stmt = $pdo->prepare("
                INSERT INTO user_badges (user_id, badge_id)
                SELECT ?, id FROM badges WHERE name = 'Mission Master'
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $earned_badges[] = 'Mission Master';
        }
    }

    // 2. Hardcore Hero Badge (3 hard missions)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as hard_count
        FROM user_missions um
        JOIN missions m ON um.mission_id = m.id
        WHERE um.user_id = ? AND um.status = 'completed' AND m.difficulty_level = 'hard'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $hard_count = $stmt->fetch()['hard_count'];
    
    if ($hard_count >= 3) {
        $stmt = $pdo->prepare("
            SELECT 1 FROM user_badges ub
            JOIN badges b ON ub.badge_id = b.id
            WHERE ub.user_id = ? AND b.name = 'Hardcore Hero'
        ");
        $stmt->execute([$_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("
                INSERT INTO user_badges (user_id, badge_id)
                SELECT ?, id FROM badges WHERE name = 'Hardcore Hero'
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $earned_badges[] = 'Hardcore Hero';
        }
    }

    // 3. Point Collector Badge (1000 points)
    $stmt = $pdo->prepare("
        SELECT points FROM users WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $total_points = $stmt->fetch()['points'];
    
    if ($total_points >= 1000) {
        $stmt = $pdo->prepare("
            SELECT 1 FROM user_badges ub
            JOIN badges b ON ub.badge_id = b.id
            WHERE ub.user_id = ? AND b.name = 'Point Collector'
        ");
        $stmt->execute([$_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare("
                INSERT INTO user_badges (user_id, badge_id)
                SELECT ?, id FROM badges WHERE name = 'Point Collector'
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $earned_badges[] = 'Point Collector';
        }
    }

    // Commit transaction
    $pdo->commit();

    // Prepare success message with badge notifications
    $success_message = 'Mission completed! You earned ' . $mission['points_reward'] . ' points.';
    if (!empty($earned_badges)) {
        $success_message .= ' 🏆 New badges earned: ' . implode(', ', $earned_badges);
    }
    $_SESSION['success'] = $success_message;
    header('Location: missions.php');
    exit;

} catch (PDOException $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = 'An error occurred while completing the mission.';
    header('Location: missions.php');
    exit;
} 