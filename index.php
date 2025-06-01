<?php
require_once 'config/config.php';
session_start();

// If user is logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?> - The Time Travel Social Network</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Orbitron', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #e2e8f0;
        }
        .timeline-glow {
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.3);
        }
        .cyber-border {
            border: 1px solid rgba(0, 255, 255, 0.3);
            position: relative;
            transition: all 0.3s ease;
        }
        .cyber-border:hover {
            transform: translateY(-5px);
            box-shadow: 0 0 30px rgba(0, 255, 255, 0.5);
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
            opacity: 0.5;
            transition: opacity 0.3s ease;
        }
        .cyber-border:hover::before {
            opacity: 0.8;
        }
        .alien-text {
            background: linear-gradient(45deg, #00ffff, #00ff00);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 10px rgba(0, 255, 255, 0.3);
        }
        .story-section {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            margin: 2rem 0;
            position: relative;
            overflow: hidden;
        }
        .story-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at center, rgba(0, 255, 255, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Navigation -->
    <nav class="bg-gray-900 bg-opacity-50 backdrop-blur-lg border-b border-cyan-500/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <div class="text-2xl font-bold alien-text">
                    Project Kairos
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="dashboard.php" class="text-cyan-400 hover:text-cyan-300">Dashboard</a>
                        <a href="logout.php" class="text-cyan-400 hover:text-cyan-300">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="text-cyan-400 hover:text-cyan-300">Login</a>
                        <a href="register.php" class="bg-cyan-500 text-white px-4 py-2 rounded-md hover:bg-cyan-600 transition-colors">
                            Join the Timeline
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
            <div class="text-center">
                <h1 class="text-6xl font-bold alien-text mb-8 floating">
                    Welcome to Kairos
                </h1>
                <p class="text-xl text-gray-300 mb-12 max-w-3xl mx-auto">
                    Step into the future where time is no longer a barrier. Join the network of time travelers 
                    and become part of something greater than yourself.
                </p>
                <div class="space-x-4">
                    <a href="register.php" class="bg-cyan-500 text-white px-8 py-3 rounded-md hover:bg-cyan-600 transition-colors inline-block cyber-border">
                        Begin Your Journey
                    </a>
                    <a href="#story" class="text-cyan-400 hover:text-cyan-300 inline-block" onclick="document.getElementById('story').scrollIntoView({behavior: 'smooth', block: 'center'}); return false;">
                        Discover Our Story
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Story Section -->
    <div id="story" class="py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="story-section">
                <h2 class="text-4xl font-bold alien-text text-center mb-12">🔮 The Origin of Kairos</h2>
                <div class="space-y-6 text-lg text-gray-300">
                    <p>In the year 2085, Scientist <span class="font-bold" style="background: linear-gradient(45deg, #ff00ff, #00ffff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-shadow: 0 0 10px rgba(255, 0, 255, 0.5);">Kazi Neyamul Hasan</span>, a brilliant computer scientist and engineer, dared to chase the impossible — time travel. His invention, called Kairos, would become the gateway to humanity's future.</p>
                    <p>But he didn't build it alone.</p>
                    <p>From the distant galaxy <span class="font-bold" style="background: linear-gradient(45deg, #ff00ff, #00ffff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-shadow: 0 0 10px rgba(255, 0, 255, 0.5);">Messier 87</span> came a peaceful alien species known as the <span class="font-bold" style="background: linear-gradient(45deg, #ff00ff, #00ffff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-shadow: 0 0 10px rgba(255, 0, 255, 0.5);">Ziramin</span> — thoughtful beings who believed in the power of human creativity. They found something special in <span class="font-bold" style="background: linear-gradient(45deg, #ff00ff, #00ffff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-shadow: 0 0 10px rgba(255, 0, 255, 0.5);">Kazi</span>… and offered to help.</p>
                    <p>Together, they unlocked secrets of space and time, blending human engineering with alien knowledge. Kairos was born — a shimmering engine capable of bending reality itself.</p>
                    <p>With their help, <span class="font-bold" style="background: linear-gradient(45deg, #ff00ff, #00ffff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-shadow: 0 0 10px rgba(255, 0, 255, 0.5);">Kazi</span> not only built the first time machine — he opened a new era for humanity. The <span class="font-bold" style="background: linear-gradient(45deg, #ff00ff, #00ffff); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-shadow: 0 0 10px rgba(255, 0, 255, 0.5);">Ziramin</span> stayed, continuing to help Earth heal, grow, and imagine a better tomorrow.</p>
                    <p class="text-center text-2xl font-bold alien-text mt-8">Welcome to the story of Kairos.<br>Welcome to the future.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div id="features" class="py-24 bg-gray-900 bg-opacity-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold alien-text text-center mb-16">
                Timeline Features
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Time Traveler Profiles -->
                <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50">
                    <h3 class="text-xl font-bold alien-text mb-4">Time Traveler Profiles</h3>
                    <p class="text-gray-300">Create your unique identity as a time traveler. Choose your origin year, species, and timeline affiliation.</p>
                </div>

                <!-- ChronoFeed -->
                <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50">
                    <h3 class="text-xl font-bold alien-text mb-4">Kairos Feed</h3>
                    <p class="text-gray-300">Share your experiences across time with other travelers. Post updates, discoveries, and temporal anomalies.</p>
                </div>

                <!-- Temporal Chat -->
                <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50">
                    <h3 class="text-xl font-bold alien-text mb-4">Temporal Chat</h3>
                    <p class="text-gray-300">Join themed chat rooms where time travelers from different eras can communicate and share knowledge.</p>
                </div>

                <!-- Mission Board -->
                <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50">
                    <h3 class="text-xl font-bold alien-text mb-4">Mission Board</h3>
                    <p class="text-gray-300">Complete missions to earn points and unlock new areas. Help maintain the timeline's integrity.</p>
                </div>

                <!-- Dynamic UI -->
                <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50">
                    <h3 class="text-xl font-bold alien-text mb-4">Dynamic UI</h3>
                    <p class="text-gray-300">Experience an interface that adapts to your timeline. Different eras get unique visual themes.</p>
                </div>

                <!-- Easter Eggs -->
                <div class="cyber-border p-6 rounded-lg bg-gray-800 bg-opacity-50">
                    <h3 class="text-xl font-bold alien-text mb-4">Easter Eggs</h3>
                    <p class="text-gray-300">Discover hidden lore, mysterious profiles, and encoded messages from the Time Core.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 border-t border-cyan-500/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center text-gray-400">
                <p>&copy; <?php echo date('Y'); ?> Project Kairos. All timelines reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html> 