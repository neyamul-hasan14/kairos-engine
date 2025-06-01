<?php
require_once 'config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user ID from URL or use logged-in user's ID
$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['user_id'];

try {
    // Get user data
    $stmt = $pdo->prepare("
        SELECT u.*,
               COUNT(DISTINCT um.id) as total_missions,
               COUNT(DISTINCT CASE WHEN um.status = 'completed' THEN um.id END) as completed_missions,
               COUNT(DISTINCT ub.badge_id) as total_badges
        FROM users u
        LEFT JOIN user_missions um ON u.id = um.user_id
        LEFT JOIN user_badges ub ON u.id = ub.user_id
        WHERE u.id = ?
        GROUP BY u.id
    ");
    $stmt->execute([$profile_id]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['error'] = 'User not found.';
        header('Location: profiles.php');
        exit;
    }

    // Get user's recent posts
    $stmt = $pdo->prepare("
        SELECT * FROM posts 
        WHERE user_id = ? 
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $stmt->execute([$profile_id]);
    $posts = $stmt->fetchAll();

    // Get user's completed missions
    $stmt = $pdo->prepare("
        SELECT m.*, um.completed_at
        FROM missions m
        JOIN user_missions um ON m.id = um.mission_id
        WHERE um.user_id = ? AND um.status = 'completed'
        ORDER BY um.completed_at DESC
        LIMIT 5
    ");
    $stmt->execute([$profile_id]);
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
    <title><?php echo htmlspecialchars($user['username']); ?>'s Profile - <?php echo APP_NAME; ?></title>
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

    <!-- Profile Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Sidebar - Profile Info -->
            <div class="lg:col-span-1">
                <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50">
                    <div class="text-center mb-6">
                        <?php if ($user['profile_image']): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" 
                                 alt="Profile" 
                                 class="w-32 h-32 mx-auto mb-4 rounded-full border-2 border-cyan-500">
                        <?php else: ?>
                            <div class="w-32 h-32 mx-auto mb-4 rounded-full bg-cyan-500/20 flex items-center justify-center">
                                <span class="text-4xl text-cyan-400">
                                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                        <h2 class="text-2xl font-bold text-cyan-400"><?php echo htmlspecialchars($user['username']); ?></h2>
                        <p class="text-gray-400">From Year <?php echo $user['origin_year']; ?></p>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <h3 class="text-sm font-medium text-cyan-400">Timeline Affiliation</h3>
                            <p class="text-gray-300"><?php echo htmlspecialchars($user['timeline_affiliation'] ?? 'Not specified'); ?></p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-cyan-400">Species</h3>
                            <p class="text-gray-300"><?php echo htmlspecialchars($user['species'] ?? 'Not specified'); ?></p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-cyan-400">Points</h3>
                            <p class="text-gray-300"><?php echo $user['points']; ?></p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-cyan-400">Email</h3>
                            <p class="text-blue-700"><?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-cyan-400">Missions</h3>
                            <p class="text-gray-300"><?php echo $user['completed_missions']; ?>/<?php echo $user['total_missions']; ?> Completed</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-cyan-400">Badges</h3>
                            <p class="text-gray-300"><?php echo $user['total_badges']; ?> Earned</p>
                        </div>
                        <?php if ($user['backstory']): ?>
                            <div>
                                <h3 class="text-sm font-medium text-cyan-400">Backstory</h3>
                                <p class="text-gray-300"><?php echo nl2br(htmlspecialchars($user['backstory'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Recent Posts -->
                <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50">
                    <h3 class="text-xl font-bold text-cyan-400 mb-4">Recent Timeline Posts</h3>
                    <?php if (!empty($posts)): ?>
                        <div class="space-y-4">
                            <?php foreach ($posts as $post): ?>
                                <div class="border-b border-cyan-500/20 pb-4 last:border-0 last:pb-0">
                                    <p class="text-gray-300 mb-2"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                                    <div class="flex items-center text-sm text-gray-400">
                                        <span><?php echo date('M j, Y g:i A', strtotime($post['created_at'])); ?></span>
                                        <?php if ($post['timeline_tag']): ?>
                                            <span class="mx-2">•</span>
                                            <span class="text-cyan-400">#<?php echo htmlspecialchars($post['timeline_tag']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-400">No posts yet.</p>
                    <?php endif; ?>
                </div>

                <!-- Completed Missions -->
                <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50">
                    <h3 class="text-xl font-bold text-cyan-400 mb-4">Recent Completed Missions</h3>
                    <?php if (!empty($completed_missions)): ?>
                        <div class="space-y-4">
                            <?php foreach ($completed_missions as $mission): ?>
                                <div class="border-b border-cyan-500/20 pb-4 last:border-0 last:pb-0">
                                    <h4 class="text-cyan-400 font-medium"><?php echo htmlspecialchars($mission['title']); ?></h4>
                                    <p class="text-gray-300 mb-2"><?php echo nl2br(htmlspecialchars($mission['description'])); ?></p>
                                    <div class="flex items-center text-sm text-gray-400">
                                        <span>Completed: <?php echo date('M j, Y', strtotime($mission['completed_at'])); ?></span>
                                        <span class="mx-2">•</span>
                                        <span class="text-cyan-400"><?php echo $mission['points_reward']; ?> Points</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-400">No completed missions yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 