<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar que el archivo de Composer existe
if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo json_encode(['success' => false, 'message' => 'Autoload not found. Please install Composer dependencies.']);
    exit();
}

require __DIR__ . '/../vendor/autoload.php'; // Cargar autoload de Composer
require 'conexion.php';
require 'mailer.php'; // Función para enviar el correo de confirmación

// Establecer el tipo de respuesta como JSON
header('Content-Type: application/json');
// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Obtener y limpiar los datos del formulario
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validar que todos los campos estén llenos
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        exit();
    }

    // Validar la fortaleza de la contraseña
    if (!preg_match('/(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/', $password)) {
        echo json_encode(['success' => false, 'message' => 'Password does not meet security requirements.']);
        exit();
    }

    try {
        // Comprobar si el email ya está registrado
        $stmt = $pdo->prepare("SELECT id FROM user WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        if ($stmt->fetch()) {
            echo json_encode(['success' => false, 'message' => 'Email already registered.']);
            exit();
        }

        // Generar un token seguro
        $token = bin2hex(random_bytes(32));

        // Hash de la contraseña
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Insertar el usuario
        $stmt = $pdo->prepare("INSERT INTO user (username, email, password, confirmed, confirm_token) VALUES (:username, :email, :password, 0, :token)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $passwordHash);
        $stmt->bindParam(':token', $token);
        $stmt->execute();

        // URL de confirmación
        $confirmUrl = "https://192.168.1.147/backend/app/confirm.php?token=$token";

        // Enviar el correo de confirmación
        if (sendConfirmationEmail($email, $confirmUrl, $username)) {
            echo json_encode(['success' => true, 'message' => 'User registered. Check your email to confirm your account.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error sending confirmation email.']);
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit();
} else {
    // Si no es un POST, devolver error 405
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit();
}
?>

