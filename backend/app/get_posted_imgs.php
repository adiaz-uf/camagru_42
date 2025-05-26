<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'conexion.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS, GET');
header('Access-Control-Allow-Headers: Content-Type');
session_start();

try {
$stmt = $pdo->prepare("SELECT
        image.id,
        image.image_url,
        image.caption,
        image.created_at,
        user.username,
        -- Count likes per image
        COALESCE(likes_count.likes, 0) AS likes_count,
        -- Count comments per image
        COALESCE(comments_count.comments, 0) AS comments_count
        FROM image
        JOIN user ON image.user_id = user.id
        LEFT JOIN (
            SELECT image_id, COUNT(*) AS likes
            FROM `like`
            GROUP BY image_id
        ) AS likes_count ON likes_count.image_id = image.id
        LEFT JOIN (
            SELECT image_id, COUNT(*) AS comments
            FROM comment
            GROUP BY image_id
        ) AS comments_count ON comments_count.image_id = image.id
        WHERE image.posted = 1
        ORDER BY image.created_at DESC
    ");
    $stmt->execute();

    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
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
