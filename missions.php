<?php
require_once 'config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    // Get available missions
    $stmt = $pdo->prepare("
        SELECT m.*, 
               CASE WHEN um.id IS NOT NULL THEN um.status ELSE 'not_started' END as user_status
        FROM missions m
        LEFT JOIN user_missions um ON m.id = um.mission_id AND um.user_id = ?
        ORDER BY m.difficulty_level, m.points_reward DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $missions = $stmt->fetchAll();

    // Get user's active missions
    $stmt = $pdo->prepare("
        SELECT m.*, um.status, um.started_at
        FROM missions m
        JOIN user_missions um ON m.id = um.mission_id
        WHERE um.user_id = ? AND um.status = 'in_progress'
        ORDER BY um.started_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $active_missions = $stmt->fetchAll();

    // Get user's completed missions
    $stmt = $pdo->prepare("
        SELECT m.*, um.completed_at
        FROM missions m
        JOIN user_missions um ON m.id = um.mission_id
        WHERE um.user_id = ? AND um.status = 'completed'
        ORDER BY um.completed_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $completed_missions = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Missions - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Orbitron', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #e2e8f0;
        }
        .cyber-border {
            border: 1px solid rgba(0, 255, 255, 0.3);
            position: relative;
        }
        .cyber-border::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #00ffff, #00ff00);
            z-index: -1;
            filter: blur(5px);
        }
        .mission-card {
            transition: transform 0.3s ease;
        }
        .mission-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gray-900 bg-opacity-50 backdrop-blur-lg border-b border-cyan-500/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="text-2xl font-bold text-cyan-400">
                        <?php echo APP_NAME; ?>
                    </a>
                </div>
                <div class="flex items-center">
                    <a href="dashboard.php" class="text-cyan-400 hover:text-cyan-300">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Missions Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Active Missions -->
        <?php if (!empty($active_missions)): ?>
            <div class="mb-12">
                <h2 class="text-2xl font-bold text-cyan-400 mb-6">Active Missions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($active_missions as $mission): ?>
                        <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50 mission-card">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-bold text-cyan-400"><?php echo htmlspecialchars($mission['title']); ?></h3>
                                <span class="px-2 py-1 text-sm rounded-full bg-yellow-500/20 text-yellow-400">
                                    In Progress
                                </span>
                            </div>
                            <p class="text-gray-300 mb-4"><?php echo nl2br(htmlspecialchars($mission['description'])); ?></p>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-cyan-400"><?php echo $mission['points_reward']; ?> Points</span>
                                <span class="text-gray-400">Started: <?php echo date('M j, Y', strtotime($mission['started_at'])); ?></span>
                            </div>
                            <div class="mt-4">
                                <form action="complete_mission.php" method="POST" class="inline">
                                    <input type="hidden" name="mission_id" value="<?php echo $mission['id']; ?>">
                                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                                        Complete Mission
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Available Missions -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-cyan-400 mb-6">Available Missions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($missions as $mission): ?>
                    <?php if ($mission['user_status'] === 'not_started'): ?>
                        <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50 mission-card">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-bold text-cyan-400"><?php echo htmlspecialchars($mission['title']); ?></h3>
                                <span class="px-2 py-1 text-sm rounded-full 
                                    <?php
                                    switch ($mission['difficulty_level']) {
                                        case 'easy':
                                            echo 'bg-green-500/20 text-green-400';
                                            break;
                                        case 'medium':
                                            echo 'bg-yellow-500/20 text-yellow-400';
                                            break;
                                        case 'hard':
                                            echo 'bg-red-500/20 text-red-400';
                                            break;
                                    }
                                    ?>">
                                    <?php echo ucfirst($mission['difficulty_level']); ?>
                                </span>
                            </div>
                            <p class="text-gray-300 mb-4"><?php echo nl2br(htmlspecialchars($mission['description'])); ?></p>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-cyan-400"><?php echo $mission['points_reward']; ?> Points</span>
                            </div>
                            <div class="mt-4">
                                <form action="start_mission.php" method="POST" class="inline">
                                    <input type="hidden" name="mission_id" value="<?php echo $mission['id']; ?>">
                                    <button type="submit" class="w-full px-4 py-2 bg-cyan-600 text-white rounded-md hover:bg-cyan-700 transition-colors">
                                        Accept Mission
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Completed Missions -->
        <?php if (!empty($completed_missions)): ?>
            <div>
                <h2 class="text-2xl font-bold text-cyan-400 mb-6">Completed Missions</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($completed_missions as $mission): ?>
                        <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50 mission-card">
                            <div class="flex justify-between items-start mb-4">
                                <h3 class="text-xl font-bold text-cyan-400"><?php echo htmlspecialchars($mission['title']); ?></h3>
                                <span class="px-2 py-1 text-sm rounded-full bg-green-500/20 text-green-400">
                                    Completed
                                </span>
                            </div>
                            <p class="text-gray-300 mb-4"><?php echo nl2br(htmlspecialchars($mission['description'])); ?></p>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-cyan-400"><?php echo $mission['points_reward']; ?> Points</span>
                                <span class="text-gray-400">Completed: <?php echo date('M j, Y', strtotime($mission['completed_at'])); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Earned Badges Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h2 class="text-2xl font-bold text-cyan-400 mb-6">Earned Badges</h2>
        
        <!-- Badge Requirements -->
        <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50 mb-8">
            <h3 class="text-xl font-bold text-cyan-400 mb-4">How to Earn Badges</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php
                // Get user's progress for each badge type
                // Mission Master Progress
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as completed_count
                    FROM user_missions
                    WHERE user_id = ? AND status = 'completed'
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $mission_master_progress = $stmt->fetch()['completed_count'];

                // Hardcore Hero Progress
                $stmt = $pdo->prepare("
                    SELECT COUNT(*) as hard_count
                    FROM user_missions um
                    JOIN missions m ON um.mission_id = m.id
                    WHERE um.user_id = ? AND um.status = 'completed' AND m.difficulty_level = 'hard'
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $hardcore_hero_progress = $stmt->fetch()['hard_count'];

                // Point Collector Progress
                $stmt = $pdo->prepare("
                    SELECT points FROM users WHERE id = ?
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $point_collector_progress = $stmt->fetch()['points'];

                // Check which badges are already earned
                $stmt = $pdo->prepare("
                    SELECT b.name
                    FROM badges b
                    JOIN user_badges ub ON b.id = ub.badge_id
                    WHERE ub.user_id = ?
                ");
                $stmt->execute([$_SESSION['user_id']]);
                $earned_badges = $stmt->fetchAll(PDO::FETCH_COLUMN);
                ?>

                <div class="p-4 border border-cyan-500/20 rounded-lg <?php echo in_array('Mission Master', $earned_badges) ? 'bg-cyan-500/10' : ''; ?>">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="text-lg font-bold text-cyan-400">Mission Master</h4>
                        <?php if (in_array('Mission Master', $earned_badges)): ?>
                            <span class="text-green-400">✓ Earned</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-gray-300 text-sm mb-2">Complete 10 missions of any difficulty</p>
                    <div class="w-full bg-gray-700 rounded-full h-2.5">
                        <div class="bg-cyan-500 h-2.5 rounded-full" style="width: <?php echo min(($mission_master_progress / 10) * 100, 100); ?>%"></div>
                    </div>
                    <p class="text-gray-400 text-xs mt-1"><?php echo $mission_master_progress; ?>/10 missions completed</p>
                </div>

                <div class="p-4 border border-cyan-500/20 rounded-lg <?php echo in_array('Hardcore Hero', $earned_badges) ? 'bg-cyan-500/10' : ''; ?>">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="text-lg font-bold text-cyan-400">Hardcore Hero</h4>
                        <?php if (in_array('Hardcore Hero', $earned_badges)): ?>
                            <span class="text-green-400">✓ Earned</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-gray-300 text-sm mb-2">Complete 3 hard difficulty missions</p>
                    <div class="w-full bg-gray-700 rounded-full h-2.5">
                        <div class="bg-cyan-500 h-2.5 rounded-full" style="width: <?php echo min(($hardcore_hero_progress / 3) * 100, 100); ?>%"></div>
                    </div>
                    <p class="text-gray-400 text-xs mt-1"><?php echo $hardcore_hero_progress; ?>/3 hard missions completed</p>
                </div>

                <div class="p-4 border border-cyan-500/20 rounded-lg <?php echo in_array('Point Collector', $earned_badges) ? 'bg-cyan-500/10' : ''; ?>">
                    <div class="flex justify-between items-center mb-2">
                        <h4 class="text-lg font-bold text-cyan-400">Point Collector</h4>
                        <?php if (in_array('Point Collector', $earned_badges)): ?>
                            <span class="text-green-400">✓ Earned</span>
                        <?php endif; ?>
                    </div>
                    <p class="text-gray-300 text-sm mb-2">Earn 1000 points from missions</p>
                    <div class="w-full bg-gray-700 rounded-full h-2.5">
                        <div class="bg-cyan-500 h-2.5 rounded-full" style="width: <?php echo min(($point_collector_progress / 1000) * 100, 100); ?>%"></div>
                    </div>
                    <p class="text-gray-400 text-xs mt-1"><?php echo $point_collector_progress; ?>/1000 points earned</p>
                </div>
            </div>
        </div>

        <!-- Earned Badges Display -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php
            // Get user's earned badges
            $stmt = $pdo->prepare("
                SELECT b.*, ub.earned_at
                FROM badges b
                JOIN user_badges ub ON b.id = ub.badge_id
                WHERE ub.user_id = ?
                ORDER BY ub.earned_at DESC
            ");
            $stmt->execute([$_SESSION['user_id']]);
            $earned_badges = $stmt->fetchAll();

            if (!empty($earned_badges)):
                foreach ($earned_badges as $badge):
            ?>
                <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50">
                    <div class="flex items-center space-x-4">
                        <?php if (isset($badge['image_url']) && $badge['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($badge['image_url']); ?>" 
                                 alt="<?php echo htmlspecialchars($badge['name']); ?>" 
                                 class="w-16 h-16 rounded-full border-2 border-cyan-500">
                        <?php else: ?>
                            <div class="w-16 h-16 rounded-full bg-cyan-500/20 flex items-center justify-center">
                                <span class="text-2xl text-cyan-400">🏆</span>
                            </div>
                        <?php endif; ?>
                        <div>
                            <h3 class="text-xl font-bold text-cyan-400"><?php echo htmlspecialchars($badge['name']); ?></h3>
                            <p class="text-gray-300 text-sm"><?php echo htmlspecialchars($badge['description']); ?></p>
                            <p class="text-gray-400 text-sm mt-1">Earned: <?php echo date('M j, Y', strtotime($badge['earned_at'])); ?></p>
                        </div>
                    </div>
                </div>
            <?php 
                endforeach;
            else:
            ?>
                <div class="col-span-full cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50">
                    <p class="text-gray-400 text-center">No badges earned yet. Complete missions to earn badges!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 