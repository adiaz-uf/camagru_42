<?php
require 'conexion.php';

$image_id = $_GET['image_id'] ?? null;

if (!$image_id) {
    echo json_encode(['success' => false, 'message' => 'No image ID']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT c.content, c.created_at, u.username 
        FROM comment c
        JOIN user u ON c.user_id = u.id
        WHERE c.image_id = ?
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$image_id]);
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'comments' => $comments]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}