<?php
session_start();
require_once 'db.php';

$pdo = getDB();

$correo   = trim($_POST['email']    ?? '');
$password = trim($_POST['password'] ?? '');

if (!$correo || !$password) {
    http_response_code(400);
    echo "Completa todos los campos";
    exit;
}

try {
    $stmt = $pdo->prepare(
        "SELECT nombre, password, codigo FROM usuarios WHERE correo = :correo"
    );
    $stmt->execute([':correo' => $correo]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo "Usuario no encontrado";
        exit;
    }

    if (password_verify($password, $user['password'])) {
        $_SESSION['usuario'] = $user['nombre'];
        $_SESSION['codigo']  = $user['codigo'];
        $_SESSION['logged_in'] = true;
        echo "Login exitoso";
    } else {
        http_response_code(401);
        echo "Contraseña incorrecta";
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo "Error del servidor";
}
