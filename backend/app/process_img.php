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
$stickerName = $_POST['sticker'] ?? '';
$posX = isset($_POST['posX']) ? (int) $_POST['posX'] : null;
$posY = isset($_POST['posY']) ? (int) $_POST['posY'] : null;
$stickerWidth = isset($_POST['stickerWidth']) ? (int) $_POST['stickerWidth'] : null;
$stickerHeight = isset($_POST['stickerHeight']) ? (int) $_POST['stickerHeight'] : null;

if ($posX === null || $posY === null || $stickerName === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Sticker or coordinates missing.']);
    exit();
}

$maxFileSize = 5 * 1024 * 1024;
$allowedExtensions = ['jpg', 'jpeg', 'png'];
$allowedMimeTypes = ['image/jpeg', 'image/png'];
$uploadDir = realpath(__DIR__ . '/../uploads') . '/';
$stickerDir = realpath(__DIR__ . '../../frontend/public/images') . '/';

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

$stickerName = basename($stickerName);
$stickerPath = '/stickers/' . basename($stickerName);

if (!file_exists($stickerPath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Sticker not found.']);
    exit();
}

$tmpPath = $file['tmp_name'];

if (!file_exists($tmpPath)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Uploaded file does not exist.']);
    exit();
}

$imgContent = file_get_contents($tmpPath);
if (!$imgContent) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Failed to read uploaded image file.']);
    exit();
}

switch ($mimeType) {
    case 'image/jpeg':
        $baseImage = @imagecreatefromjpeg($tmpPath);
        break;
    case 'image/png':
        $baseImage = @imagecreatefrompng($tmpPath);
        break;
    default:
        http_response_code(415);
        echo json_encode(['success' => false, 'message' => 'Unsupported image format.']);
        exit();
}

$baseImage = @imagecreatefromstring($imgContent);
if (!$baseImage) {
    http_response_code(415);
    echo json_encode(['success' => false, 'message' => 'Could not read base image. Possibly invalid or corrupt file.']);
    exit();
}

$stickerExt = strtolower(pathinfo($stickerPath, PATHINFO_EXTENSION));
switch ($stickerExt) {
    case 'png':
        $sticker = @imagecreatefrompng($stickerPath);
        break;
    case 'jpg':
    case 'jpeg':
        $sticker = @imagecreatefromjpeg($stickerPath);
        break;
    default:
        imagedestroy($baseImage);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Unsupported sticker format.']);
        exit();
}

if (!$sticker) {
    imagedestroy($baseImage);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to load sticker image.']);
    exit();
}

if ($stickerWidth && $stickerHeight) {
    $resizedSticker = imagecreatetruecolor($stickerWidth, $stickerHeight);
    imagealphablending($resizedSticker, false);
    imagesavealpha($resizedSticker, true);
    imagecopyresampled(
        $resizedSticker,
        $sticker,
        0, 0, 0, 0,
        $stickerWidth, $stickerHeight,
        imagesx($sticker),
        imagesy($sticker)
    );
    imagedestroy($sticker);
    $sticker = $resizedSticker;
}

imagealphablending($baseImage, true);
imagesavealpha($baseImage, true);
imagecopy($baseImage, $sticker, $posX, $posY, 0, 0, imagesx($sticker), imagesy($sticker));

try {
    $newFileName = bin2hex(random_bytes(8)) . ".png";
    $targetPath = $uploadDir . $newFileName;

    if (!imagepng($baseImage, $targetPath)) {
        throw new Exception('Error saving the merged image.');
    }

    $imageUrl = "/uploads/" . $newFileName;

    imagedestroy($baseImage);
    imagedestroy($sticker);

    $stmt = $pdo->prepare("INSERT INTO image (user_id, image_url) VALUES (:user_id, :image_url)");
    $stmt->execute([
        ':user_id' => $userId,
        ':image_url' => $imageUrl
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Image created successfully.',
        'image_url' => $imageUrl
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>
