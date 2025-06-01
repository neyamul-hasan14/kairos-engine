<?php
require_once 'config/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch chat rooms
$stmt = $pdo->query("SELECT * FROM chat_rooms ORDER BY created_at DESC");
$rooms = $stmt->fetchAll();

// Get selected room
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : ($rooms[0]['id'] ?? null);
if ($room_id) {
    $stmt = $pdo->prepare("SELECT * FROM chat_rooms WHERE id = ?");
    $stmt->execute([$room_id]);
    $current_room = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat - <?php echo APP_NAME; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Orbitron', sans-serif; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: #e2e8f0; }
        .cyber-border { border: 1px solid rgba(0, 255, 255, 0.3); position: relative; }
        .cyber-border::before { content: ''; position: absolute; top: -2px; left: -2px; right: -2px; bottom: -2px; background: linear-gradient(45deg, #00ffff, #00ff00); z-index: -1; filter: blur(5px); }
        .scrollbar::-webkit-scrollbar { width: 8px; background: #22223b; }
        .scrollbar::-webkit-scrollbar-thumb { background: #00bcd4; border-radius: 4px; }
    </style>
</head>
<body class="min-h-screen">
    <div class="flex h-screen">
        <!-- Room List -->
        <div class="w-1/4 bg-gray-900 bg-opacity-60 cyber-border p-4 flex flex-col">
            <a href="dashboard.php" class="mb-4 inline-block px-4 py-2 bg-cyan-600 text-white rounded hover:bg-cyan-700 font-bold transition-colors">&larr; Back to Dashboard</a>
            <h2 class="text-xl font-bold text-cyan-400 mb-4">Chat Rooms</h2>
            <input type="text" id="room-search" placeholder="Search rooms..." class="mb-3 px-2 py-1 rounded bg-gray-700 text-cyan-300 focus:outline-none" oninput="filterRooms()">
            <div class="flex-1 overflow-y-auto scrollbar space-y-2" id="room-list">
                <?php foreach ($rooms as $room): ?>
                    <a href="?room_id=<?php echo $room['id']; ?>" class="block px-4 py-2 rounded-lg <?php echo ($room['id'] == $room_id) ? 'bg-cyan-600 text-white' : 'bg-gray-800 text-cyan-400 hover:bg-cyan-700'; ?> font-semibold transition-colors room-link">
                        <?php echo htmlspecialchars($room['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            <form action="create_room.php" method="POST" class="mt-4 flex space-x-2">
                <input type="text" name="name" placeholder="New Room" class="flex-1 px-2 py-1 rounded bg-gray-700 text-cyan-300 focus:outline-none" required>
                <button type="submit" class="px-3 py-1 bg-cyan-600 text-white rounded hover:bg-cyan-700">+</button>
            </form>
        </div>
        <!-- Chat Area -->
        <div class="flex-1 flex flex-col bg-gray-800 bg-opacity-60 cyber-border p-4">
            <h2 class="text-xl font-bold text-cyan-400 mb-2"><?php echo isset($current_room) ? htmlspecialchars($current_room['name']) : 'No Room Selected'; ?></h2>
            <div id="chat-messages" class="flex-1 overflow-y-auto scrollbar mb-4 p-2 bg-gray-900 bg-opacity-40 rounded-lg"></div>
            <form id="send-message-form" class="flex space-x-2">
                <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
                <input type="text" name="message" id="message-input" placeholder="Type your message..." autocomplete="off" class="flex-1 px-3 py-2 rounded bg-gray-700 text-cyan-300 focus:outline-none" required>
                <button type="submit" class="px-4 py-2 bg-cyan-600 text-white rounded hover:bg-cyan-700">Send</button>
            </form>
        </div>
    </div>
    <script>
    function fetchMessages() {
        const roomId = <?php echo (int)$room_id; ?>;
        fetch('fetch_messages.php?room_id=' + roomId)
            .then(res => res.text())
            .then(html => {
                document.getElementById('chat-messages').innerHTML = html;
                document.getElementById('chat-messages').scrollTop = document.getElementById('chat-messages').scrollHeight;
            });
    }
    document.getElementById('send-message-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('send_message.php', { method: 'POST', body: formData })
            .then(res => res.text())
            .then(() => {
                document.getElementById('message-input').value = '';
                fetchMessages();
            });
    });
    setInterval(fetchMessages, 2000);
    fetchMessages();

    function filterRooms() {
        const search = document.getElementById('room-search').value.toLowerCase();
        document.querySelectorAll('.room-link').forEach(link => {
            link.style.display = link.textContent.toLowerCase().includes(search) ? '' : 'none';
        });
    }
    </script>
</body>
</html> 