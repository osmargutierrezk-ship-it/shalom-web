<?php
require_once 'db.php';

$pdo = getDB();

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

try {
    // 1. Verificar que el código estudiantil existe
    $check = $pdo->prepare("SELECT id FROM estudiantes WHERE codigo = :codigo");
    $check->execute([':codigo' => $codigo]);
    if (!$check->fetch()) {
        http_response_code(400);
        echo "Código estudiantil no válido";
        exit;
    }

    // 2. Verificar que el código no esté ya registrado
    $dup = $pdo->prepare("SELECT id FROM usuarios WHERE codigo = :codigo");
    $dup->execute([':codigo' => $codigo]);
    if ($dup->fetch()) {
        http_response_code(409);
        echo "Este código ya está registrado";
        exit;
    }

    // 3. Insertar con contraseña hasheada
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        "INSERT INTO usuarios (nombre, correo, telefono, password, codigo)
         VALUES (:nombre, :correo, :telefono, :password, :codigo)"
    );
    $stmt->execute([
        ':nombre'   => $nombre,
        ':correo'   => $correo,
        ':telefono' => $telefono,
        ':password' => $hash,
        ':codigo'   => $codigo,
    ]);
    echo "Registro exitoso";

} catch (PDOException $e) {
    // Correo duplicado (unique constraint)
    if ($e->getCode() === '23505') {
        http_response_code(409);
        echo "Este correo ya está registrado";
    } else {
        http_response_code(500);
        echo "Error al registrar";
    }
}
