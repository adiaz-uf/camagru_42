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
    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
    $limit = isset($_GET['limit']) ? max(1, intval($_GET['limit'])) : 10;
    $offset = ($page - 1) * $limit;

    $stmt = $pdo->prepare("SELECT SQL_CALC_FOUND_ROWS
        image.id,
        image.image_url,
        image.caption,
        image.created_at,
        user.username,
        COALESCE(likes_count.likes, 0) AS likes_count,
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
    LIMIT :offset, :limit");

    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalStmt = $pdo->query("SELECT FOUND_ROWS()");
    $totalRows = $totalStmt->fetchColumn();
    $hasMore = ($offset + $limit) < $totalRows;
    
    echo json_encode(['success' => true,'images' => $images,'has_more' => $hasMore]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
