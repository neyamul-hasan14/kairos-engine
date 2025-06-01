<?php
require_once 'config/config.php';
session_start();
if (!isset($_SESSION['user_id'])) exit;
$room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
if (!$room_id) exit;
$stmt = $pdo->prepare("SELECT m.*, u.username FROM chat_messages m JOIN users u ON m.user_id = u.id WHERE m.room_id = ? ORDER BY m.created_at ASC LIMIT 50");
$stmt->execute([$room_id]);
$messages = $stmt->fetchAll();
foreach ($messages as $msg): ?>
    <div class="mb-2">
        <span class="text-cyan-400 font-bold"><?php echo htmlspecialchars($msg['username']); ?>:</span>
        <span class="text-gray-200"><?php echo htmlspecialchars($msg['message']); ?></span>
        <span class="text-xs text-gray-500 ml-2"><?php echo date('H:i', strtotime($msg['created_at'])); ?></span>
    </div>
<?php endforeach; ?> 