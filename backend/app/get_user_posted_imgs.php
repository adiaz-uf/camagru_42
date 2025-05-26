<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'conexion.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS, GET');
header('Access-Control-Allow-Headers: Content-Type');
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT image.id, image.image_url, image.caption, image.created_at, user.username 
                           FROM image 
                           JOIN user ON image.user_id = user.id 
                           WHERE image.posted = 1 AND image.user_id = :userId 
                           ORDER BY image.created_at DESC");
    $stmt->execute(['userId' => $userId]);

    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'images' => $images]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
