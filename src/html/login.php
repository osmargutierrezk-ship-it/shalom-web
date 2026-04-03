<?php
session_start();
require_once 'db.php';

$conn = getDB();

$correo   = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if (!$correo || !$password) {
    http_response_code(400);
    echo "Completa todos los campos";
    exit;
}

$stmt = $conn->prepare("SELECT nombre, password, codigo FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo "Usuario no encontrado";
    exit;
}

$user = $result->fetch_assoc();

if (password_verify($password, $user['password'])) {
    $_SESSION['usuario'] = $user['nombre'];
    $_SESSION['codigo']  = $user['codigo'];
    echo "Login exitoso";
} else {
    http_response_code(401);
    echo "Contraseña incorrecta";
}

$stmt->close();
$conn->close();
