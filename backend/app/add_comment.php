<?php
session_start();
require 'conexion.php';
require 'send_notification.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please, Sign In first']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$image_id = $data['image_id'];
$content = trim($data['content']);
$user_id = $_SESSION['user_id'];

if ($content === '') {
    echo json_encode(['success' => false, 'message' => 'Comment cannot be empty.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO comment (user_id, image_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $image_id, $content]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

try {
    $stmt = $pdo->prepare("SELECT u.id, u.email, u.notifications, u.username 
                       FROM image i 
                       JOIN user u ON i.user_id = u.id 
                       WHERE i.id = :image_id");
    $stmt->execute(['image_id' => $image_id]);
    $owner = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($owner && $owner['notifications']) {
        if ($_SESSION['user_id'] != $owner['id']) {
            $subject = 'New comment on your post';
            $message = "
                <p>Hello {$owner['username']},</p>
                <p>One of your posts has received a new comment:</p>
                <blockquote>{$content}</blockquote>
                <p>Sign in to view or reply.</p>
            ";
            sendNotificationEmail($owner['email'], $owner['username'], $subject, $message);
        }
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
