<?php
require_once 'config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user data
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    // Get recent posts
    $stmt = $pdo->prepare("
        SELECT p.*, u.username, u.origin_year, u.species 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        ORDER BY p.created_at DESC 
        LIMIT 10
    ");
    $stmt->execute();
    $posts = $stmt->fetchAll();

    // Get user's active missions
    $stmt = $pdo->prepare("
        SELECT m.* 
        FROM missions m 
        JOIN user_missions um ON m.id = um.mission_id 
        WHERE um.user_id = ? AND um.status = 'in_progress'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $active_missions = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo APP_NAME; ?></title>
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
                    <div class="text-2xl font-bold text-cyan-400">
                        Kairos
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="missions.php" class="text-cyan-400 hover:text-cyan-300">Missions</a>
                    <a href="profiles.php" class="text-cyan-400 hover:text-cyan-300">Profiles</a>
                    <a href="chat.php" class="text-cyan-400 hover:text-cyan-300">Chat</a>
                    <a href="logout.php" class="text-cyan-400 hover:text-cyan-300">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Dashboard Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Sidebar - User Info -->
            <div class="lg:col-span-1">
                <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50">
                    <div class="text-center mb-6">
                        <div class="w-24 h-24 mx-auto mb-4 rounded-full bg-cyan-500/20 flex items-center justify-center">
                            <span class="text-3xl text-cyan-400"><?php echo strtoupper(substr($user['username'], 0, 1)); ?></span>
                        </div>
                        <h2 class="text-xl font-bold text-cyan-400"><?php echo htmlspecialchars($user['username']); ?></h2>
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
                    </div>
                </div>
            </div>

            <!-- Main Content - Timeline Feed -->
            <div class="lg:col-span-2">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded mb-4">
                        <?php 
                        echo htmlspecialchars($_SESSION['success']);
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded mb-4">
                        <?php 
                        echo htmlspecialchars($_SESSION['error']);
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Post Creation -->
                <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50 mb-8">
                    <form action="create_post.php" method="POST" class="space-y-4">
                        <textarea name="content" rows="3" 
                                class="w-full px-3 py-2 bg-gray-700 bg-opacity-50 border border-cyan-500/20 rounded-md text-gray-300 placeholder-gray-500 focus:outline-none focus:border-cyan-500"
                                placeholder="Share your temporal experiences..."></textarea>
                        <div class="flex justify-between items-center">
                            <select name="timeline_tag" 
                                    class="px-3 py-2 bg-gray-700 bg-opacity-50 border border-cyan-500/20 rounded-md text-gray-300">
                                <option value="">Select Timeline</option>
                                <option value="past">Past Events</option>
                                <option value="present">Present Moment</option>
                                <option value="future">Future Visions</option>
                            </select>
                            <button type="submit" 
                                    class="px-4 py-2 bg-cyan-600 text-white rounded-md hover:bg-cyan-700 transition-colors">
                                Post to Timeline
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Timeline Feed -->
                <div class="space-y-6">
                    <h2 class="text-2xl font-bold text-cyan-400 mb-4">Global Timeline Feed</h2>
                    <p class="text-gray-400 mb-6">Posts from all time travelers across the multiverse</p>
                    <?php foreach ($posts as $post): ?>
                        <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-full bg-cyan-500/20 flex items-center justify-center mr-3">
                                        <span class="text-cyan-400"><?php echo strtoupper(substr($post['username'], 0, 1)); ?></span>
                                    </div>
                                    <div>
                                        <h3 class="text-cyan-400 font-medium"><?php echo htmlspecialchars($post['username']); ?></h3>
                                        <p class="text-sm text-gray-400">
                                            Year <?php echo $post['origin_year']; ?> • 
                                            <?php echo htmlspecialchars($post['species']); ?>
                                        </p>
                                    </div>
                                </div>
                                <?php if ($post['user_id'] === $_SESSION['user_id']): ?>
                                    <a href="edit_post.php?id=<?php echo (int)$post['id']; ?>" 
                                       class="text-cyan-400 hover:text-cyan-300 text-sm">
                                        Edit Post
                                    </a>
                                <?php endif; ?>
                            </div>
                            <p class="text-gray-300 mb-4"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
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
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 border-t border-cyan-500/20 mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Kairos. All timelines reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html> 