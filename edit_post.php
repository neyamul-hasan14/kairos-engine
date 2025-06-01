<?php
require_once 'config/config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$post = null;

// Get post ID from URL
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id > 0) {
    try {
        // Get post data
        $stmt = $pdo->prepare("
            SELECT content 
            FROM posts 
            WHERE id = ? AND user_id = ?
        ");
        $stmt->execute([$post_id, $_SESSION['user_id']]);
        $post = $stmt->fetch();

        if (!$post) {
            $_SESSION['error'] = 'Post not found or you do not have permission to edit it.';
            header('Location: dashboard.php');
            exit;
        }
    } catch (PDOException $e) {
        $error = 'Failed to load post.';
    }
} else {
    $_SESSION['error'] = 'Invalid post ID.';
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['content'] ?? '');

    if (empty($content)) {
        $error = 'Post content cannot be empty.';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE posts 
                SET content = ?
                WHERE id = ? AND user_id = ?
            ");
            $result = $stmt->execute([$content, $post_id, $_SESSION['user_id']]);

            if ($result && $stmt->rowCount() > 0) {
                $_SESSION['success'] = 'Post updated successfully!';
                header('Location: dashboard.php');
                exit;
            } else {
                $error = 'Failed to update post.';
            }
        } catch (PDOException $e) {
            $error = 'An error occurred.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Post - <?php echo APP_NAME; ?></title>
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
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-cyan-400 hover:text-cyan-300">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Edit Post Form -->
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50">
            <h1 class="text-2xl font-bold text-cyan-400 mb-6">Edit Post</h1>

            <?php if ($error): ?>
                <div class="bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded mb-4">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="content" class="block text-sm font-medium text-cyan-400">Post Content</label>
                    <textarea name="content" id="content" rows="4" required
                            class="mt-1 block w-full px-3 py-2 bg-gray-700 bg-opacity-50 border border-cyan-500/20 rounded-md text-gray-300 placeholder-gray-500 focus:outline-none focus:border-cyan-500"><?php echo htmlspecialchars($post['content']); ?></textarea>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="dashboard.php" 
                       class="px-4 py-2 border border-cyan-500/20 text-cyan-400 rounded-md hover:bg-cyan-500/10 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-cyan-600 text-white rounded-md hover:bg-cyan-700 transition-colors">
                        Update Post
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 