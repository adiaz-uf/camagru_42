<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if the Composer autoload file exists
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo json_encode(['success' => false, 'message' => 'Autoload not found. Please install Composer dependencies.']);
    exit();
}

require __DIR__ . '/../vendor/autoload.php'; // Load Composer autoload
require 'conexion.php';                     // Database connection
require 'mailer.php';                       // Function to send confirmation email

// Set response type to JSON
header('Content-Type: application/json');
// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit();
    }

    if (!preg_match('/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/', $password)) {
        echo json_encode(['success' => false, 'message' => 'Password does not meet security requirements.']);
        exit();
    }

    try {
        // Check if the email is already registered
        $stmt = $pdo->prepare("SELECT id FROM user WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email is already registered.']);
            exit();
        }

        // Generate a secure confirmation token
        $token = bin2hex(random_bytes(32));

        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into the database
        $stmt = $pdo->prepare("INSERT INTO user (username, email, password, confirmed, confirm_token) VALUES (:username, :email, :password, 0, :token)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        // Construct confirmation URL
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST']; // Dynamic domain or IP
        $confirmUrl = "{$protocol}{$host}/backend/app/confirm.php?token={$token}";

        // Send confirmation email
        if (sendConfirmationEmail($email, $confirmUrl, $username)) {
            echo json_encode(['success' => true, 'message' => 'User registered successfully. Please check your email to confirm your account.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to send confirmation email.']);
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}
?>
