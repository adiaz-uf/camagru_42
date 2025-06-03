<?php
session_start();

require 'conexion.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['image']) || !isset($data['caption'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

$userId = $_SESSION['user_id'];
$imageUrl = $data['image'];
$caption = trim($data['caption']);

$parsedUrl = parse_url($imageUrl);
if (!isset($parsedUrl['path'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid image URL']);
    exit;
}

$sourcePath = realpath(__DIR__ . '/../uploads/' . basename($parsedUrl['path']));
if (!file_exists($sourcePath)) {
    echo json_encode(['success' => false, 'message' => 'Image not found']);
    exit;
}

$filename = uniqid('post_', true) . '.' . pathinfo($sourcePath, PATHINFO_EXTENSION);
$destinationDir = realpath(__DIR__ . '/../uploads') . '/';
$destinationPath = $destinationDir . $filename;
$imageUrl = '/uploads/' . $filename;


if (!is_writable($destinationDir)) {
    echo json_encode(['success' => false, 'message' => 'Destination not writable: ' . $destinationDir]);
    exit;
}
if (!copy($sourcePath, $destinationPath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save post']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO image (user_id, image_url, caption, posted) VALUES (:user_id, :image_url, :caption, :posted)");
    $stmt->execute([
        ':user_id' => $userId,
        ':image_url' => $imageUrl,
        ':caption' =>  $caption,
        ':posted' => 1,
    ]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'DB error: ' . $e->getMessage()]);
}
