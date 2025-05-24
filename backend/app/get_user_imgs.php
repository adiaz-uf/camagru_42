<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'conexion.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT image_url FROM image WHERE user_id = :user_id AND posted = 0 ORDER BY created_at DESC LIMIT 20");
    $stmt->execute([':user_id' => $userId]);

    $images = $stmt->fetchAll(PDO::FETCH_COLUMN); // array de URLs
    
    if (empty($images)) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'No images yet.']);
    } else {
        echo json_encode(['success' => true, 'images' => $images]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
