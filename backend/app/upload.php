<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'conexion.php';

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized: Please log in.']);
    exit();
}

$userId = $_SESSION['user_id'];

$maxFileSize = 5 * 1024 * 1024;
$allowedExtensions = ['jpg', 'jpeg', 'png'];
$allowedMimeTypes = ['image/jpeg', 'image/png'];

$uploadDir = realpath(__DIR__ . '/../uploads') . '/';

if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create uploads directory.']);
    exit();
}

if (!isset($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
    exit();
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Upload error.']);
    exit();
}

if ($file['size'] > $maxFileSize) {
    http_response_code(413);
    echo json_encode(['success' => false, 'message' => 'File too large (max 5MB).']);
    exit();
}

$tmpPath = $file['tmp_name'];
$originalName = basename($file['name']);
$mimeType = mime_content_type($tmpPath);
$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($ext, $allowedExtensions) || !in_array($mimeType, $allowedMimeTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Unsupported file type.']);
    exit();
}

try {
    $newFileName = bin2hex(random_bytes(8)) . "." . $ext;
    $targetPath = $uploadDir . $newFileName;

    if (!move_uploaded_file($tmpPath, $targetPath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to move uploaded file.']);
        exit();
    }

    $imageUrl = "/uploads/" . $newFileName;

/*     $stmt = $pdo->prepare("INSERT INTO image (user_id, image_url) VALUES (:user_id, :image_url)");
    $stmt->execute([
        ':user_id' => $userId,
        ':image_url' => $imageUrl,
    ]); */

    echo json_encode(['success' => true, 'message' => 'Image uploaded successfully.', 'image_url' => $imageUrl]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
