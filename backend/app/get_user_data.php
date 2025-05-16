<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

// Responder con los datos del usuario (sacados de la sesión)
echo json_encode([
    'username' => $_SESSION['username'],
    'email' => $_SESSION['email']
]);
?>
