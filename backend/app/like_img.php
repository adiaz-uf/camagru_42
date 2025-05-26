<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$image_id = $data['image_id'];
$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM `like` WHERE user_id = ? AND image_id = ?");
    $stmt->execute([$user_id, $image_id]);
    if ($stmt->fetch()) {
        // Unlike
        $del = $pdo->prepare("DELETE FROM `like` WHERE user_id = ? AND image_id = ?");
        $del->execute([$user_id, $image_id]);
        echo json_encode(['success' => true, 'liked' => false]);
    } else {
        // Like
        $ins = $pdo->prepare("INSERT INTO `like` (user_id, image_id) VALUES (?, ?)");
        $ins->execute([$user_id, $image_id]);
        echo json_encode(['success' => true, 'liked' => true]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}