<?php
require_once 'config/config.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $origin_year = (int)($_POST['origin_year'] ?? 0);
    $timeline_affiliation = trim($_POST['timeline_affiliation'] ?? '');
    $species = trim($_POST['species'] ?? '');
    $backstory = trim($_POST['backstory'] ?? '');

    if (empty($username) || empty($email) || empty($password) || empty($origin_year)) {
        $error = 'Please fill in all required fields.';
    } else {
        try {
            // Check if username or email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->rowCount() > 0) {
                $error = 'Username or email already exists.';
            } else {
                // Create new user
                $stmt = $pdo->prepare("
                    INSERT INTO users (username, email, password_hash, origin_year, timeline_affiliation, species, backstory)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([
                    $username,
                    $email,
                    password_hash($password, PASSWORD_DEFAULT, ['cost' => HASH_COST]),
                    $origin_year,
                    $timeline_affiliation,
                    $species,
                    $backstory
                ]);

                $success = 'Registration successful! You can now log in.';
                header('Location: login.php');
                exit;
            }
        } catch (PDOException $e) {
            $error = 'Registration failed. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Orbitron', sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #e2e8f0;
        }
        .cyber-form {
            background: rgba(17, 24, 39, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 255, 255, 0.2);
            box-shadow: 0 0 20px rgba(0, 255, 255, 0.1);
        }
        .cyber-input {
            background: rgba(17, 24, 39, 0.5);
            border: 1px solid rgba(0, 255, 255, 0.2);
            transition: all 0.3s ease;
        }
        .cyber-input:focus {
            border-color: rgba(0, 255, 255, 0.5);
            box-shadow: 0 0 10px rgba(0, 255, 255, 0.2);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-bold text-cyan-400">
                Join the Timeline
            </h2>
            <p class="mt-2 text-center text-sm text-gray-400">
                Create your time traveler profile
            </p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500 bg-opacity-20 border border-red-500 text-red-300 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-500 bg-opacity-20 border border-green-500 text-green-300 px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <form class="mt-8 space-y-6 cyber-form p-8 rounded-lg" method="POST">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-cyan-400">Username</label>
                    <input id="username" name="username" type="text" required 
                           class="cyber-input appearance-none rounded-md relative block w-full px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 focus:z-10 sm:text-sm"
                           placeholder="Choose your temporal handle">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-cyan-400">Email</label>
                    <input id="email" name="email" type="email" required 
                           class="cyber-input appearance-none rounded-md relative block w-full px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 focus:z-10 sm:text-sm"
                           placeholder="Your quantum communication address">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-cyan-400">Password</label>
                    <input id="password" name="password" type="password" required 
                           class="cyber-input appearance-none rounded-md relative block w-full px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 focus:z-10 sm:text-sm"
                           placeholder="Your temporal encryption key">
                </div>

                <div>
                    <label for="origin_year" class="block text-sm font-medium text-cyan-400">Origin Year</label>
                    <input id="origin_year" name="origin_year" type="number" required 
                           class="cyber-input appearance-none rounded-md relative block w-full px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 focus:z-10 sm:text-sm"
                           placeholder="Your timeline of origin">
                </div>

                <div>
                    <label for="timeline_affiliation" class="block text-sm font-medium text-cyan-400">Timeline Affiliation</label>
                    <input id="timeline_affiliation" name="timeline_affiliation" type="text" 
                           class="cyber-input appearance-none rounded-md relative block w-full px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 focus:z-10 sm:text-sm"
                           placeholder="Your timeline branch">
                </div>

                <div>
                    <label for="species" class="block text-sm font-medium text-cyan-400">Species</label>
                    <input id="species" name="species" type="text" 
                           class="cyber-input appearance-none rounded-md relative block w-full px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 focus:z-10 sm:text-sm"
                           placeholder="Your species designation">
                </div>

                <div>
                    <label for="backstory" class="block text-sm font-medium text-cyan-400">Backstory</label>
                    <textarea id="backstory" name="backstory" rows="4" 
                              class="cyber-input appearance-none rounded-md relative block w-full px-3 py-2 text-gray-300 placeholder-gray-500 focus:outline-none focus:ring-cyan-500 focus:border-cyan-500 focus:z-10 sm:text-sm"
                              placeholder="Share your temporal journey..."></textarea>
                </div>
            </div>

            <div>
                <button type="submit" 
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-cyan-600 hover:bg-cyan-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-cyan-500">
                    Initialize Timeline
                </button>
            </div>

            <div class="text-center">
                <a href="login.php" class="font-medium text-cyan-400 hover:text-cyan-300">
                    Already have a timeline? Sign in
                </a>
            </div>
        </form>
    </div>
</body>
</html>