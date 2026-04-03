<?php
require_once 'db.php';

$conn = getDB();

$nombre   = trim($_POST['nombre']   ?? '');
$correo   = trim($_POST['correo']   ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$codigo   = trim($_POST['codigo']   ?? '');
$password = trim($_POST['password'] ?? '');

if (!$nombre || !$correo || !$telefono || !$codigo || !$password) {
    http_response_code(400);
    echo "Completa todos los campos";
    exit;
}

// 1. Verificar código estudiantil válido
$check = $conn->prepare("SELECT id FROM estudiantes WHERE codigo = ?");
$check->bind_param("s", $codigo);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    http_response_code(400);
    echo "Código estudiantil no válido";
    exit;
}
$check->close();

// 2. Verificar que el código no esté ya registrado
$dup = $conn->prepare("SELECT id FROM usuarios WHERE codigo = ?");
$dup->bind_param("s", $codigo);
$dup->execute();
if ($dup->get_result()->num_rows > 0) {
    http_response_code(409);
    echo "Este código ya está registrado";
    exit;
}
$dup->close();

// 3. Insertar nuevo usuario con contraseña hasheada
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare(
    "INSERT INTO usuarios (nombre, correo, telefono, password, codigo) VALUES (?, ?, ?, ?, ?)"
);
$stmt->bind_param("sssss", $nombre, $correo, $telefono, $hash, $codigo);

if ($stmt->execute()) {
    echo "Registro exitoso";
} else {
    http_response_code(500);
    echo "Error al registrar";
}

$stmt->close();
$conn->close();
