<?php
session_start();
require 'conexion.php';

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