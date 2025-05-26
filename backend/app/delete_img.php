<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'conexion.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Only POST allowed']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$imageId = $input['image_id'] ?? null;
$userId = $_SESSION['user_id'];

if (!$imageId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Image ID is required']);
    exit();
}

try {
    $stmt = $pdo->prepare("DELETE FROM image WHERE id = :id AND user_id = :userId");
    $stmt->execute([
        'id' => $imageId,
        'userId' => $userId
    ]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Image not found or not yours']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}