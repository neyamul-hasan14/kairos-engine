<?php
require_once 'config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    // Get all users with their mission and badge counts
    $stmt = $pdo->prepare("
        SELECT 
            u.*,
            COUNT(DISTINCT um.id) as total_missions,
            COUNT(DISTINCT CASE WHEN um.status = 'completed' THEN um.id END) as completed_missions,
            COUNT(DISTINCT ub.badge_id) as total_badges
        FROM users u
        LEFT JOIN user_missions um ON u.id = um.user_id
        LEFT JOIN user_badges ub ON u.id = ub.user_id
        GROUP BY u.id
        ORDER BY u.points DESC, u.created_at DESC
    ");
    $stmt->execute();
    $users = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Traveler Profiles - <?php echo APP_NAME; ?></title>
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
        .profile-card {
            transition: transform 0.3s ease;
        }
        .profile-card:hover {
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

    <!-- Profiles Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-cyan-400 mb-2">Time Traveler Profiles</h1>
            <p class="text-gray-400">Discover fellow time travelers across the multiverse</p>
        </div>

        <!-- Profiles Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($users as $user): ?>
                <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50 profile-card">
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <?php if ($user['profile_image']): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_image']); ?>" 
                                     alt="Profile" 
                                     class="w-16 h-16 rounded-full border-2 border-cyan-500">
                            <?php else: ?>
                                <div class="w-16 h-16 rounded-full bg-gray-700 flex items-center justify-center border-2 border-cyan-500">
                                    <span class="text-2xl text-cyan-400">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h3 class="text-xl font-bold text-cyan-400">
                                        <?php echo htmlspecialchars($user['username']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-400">
                                        Origin: <?php echo htmlspecialchars($user['origin_year']); ?>
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-sm rounded-full bg-cyan-500/20 text-cyan-400">
                                    <?php echo $user['points']; ?> Points
                                </span>
                            </div>
                            
                            <div class="mt-4 space-y-2">
                                <div class="flex items-center text-sm">
                                    <span class="text-gray-400 w-24">Species:</span>
                                    <span class="text-gray-300"><?php echo htmlspecialchars($user['species'] ?? 'Unknown'); ?></span>
                                </div>
                                <div class="flex items-center text-sm">
                                    <span class="text-gray-400 w-24">Timeline:</span>
                                    <span class="text-gray-300"><?php echo htmlspecialchars($user['timeline_affiliation'] ?? 'Unspecified'); ?></span>
                                </div>
                                <div class="flex items-center text-sm">
                                    <span class="text-gray-400 w-24">Missions:</span>
                                    <span class="text-gray-300"><?php echo $user['completed_missions']; ?>/<?php echo $user['total_missions']; ?> Completed</span>
                                </div>
                                <div class="flex items-center text-sm">
                                    <span class="text-gray-400 w-24">Badges:</span>
                                    <span class="text-gray-300"><?php echo $user['total_badges']; ?> Earned</span>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                                <div>
                                    <p class="text-sm text-gray-400">Points</p>
                                    <p class="text-lg font-bold text-cyan-400"><?php echo $user['points']; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">Missions</p>
                                    <p class="text-lg font-bold text-cyan-400"><?php echo $user['completed_missions']; ?>/<?php echo $user['total_missions']; ?></p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-400">Badges</p>
                                    <p class="text-lg font-bold text-cyan-400"><?php echo $user['total_badges']; ?></p>
                                </div>
                            </div>
                            <?php if (!empty($user['backstory'])): ?>
                                <div class="mt-4">
                                    <p class="text-gray-300 text-sm line-clamp-2"> <?php echo htmlspecialchars($user['backstory']); ?> </p>
                                </div>
                            <?php endif; ?>

                            <div class="mt-4">
                                <a href="profile.php?id=<?php echo $user['id']; ?>" 
                                   class="block text-center px-4 py-2 bg-cyan-600 text-white rounded-md hover:bg-cyan-700 transition-colors">
                                    View Profile
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html> 