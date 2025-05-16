<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'conexion.php';

// Set content type to JSON to handle AJAX responses
header('Content-Type: application/json'); 
// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the form data and trim extra spaces
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate if all fields are filled
    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id, username, email, password, confirmed FROM user WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            if (!$user['confirmed']) {
                echo json_encode(['success' => false, 'message' => 'Please confirm your email before logging in.']);
                exit();
            }

            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];

            echo json_encode(['success' => true, 'message' => 'Login successful.']);
            exit();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password.']);
            exit();
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        exit();
    }
} else {
    // If the method is not POST, return a 405 Method Not Allowed response
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}
?>
