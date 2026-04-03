<?php
require_once 'db.php';

$pdo = getDB();

$padre      = trim($_POST['padre']      ?? '');
$estudiante = trim($_POST['estudiante'] ?? '');
$telefono   = trim($_POST['telefono']   ?? '');
$email      = trim($_POST['email']      ?? '');
$nivel      = trim($_POST['nivel']      ?? '');
$grado      = trim($_POST['grado']      ?? '');
$mensaje    = trim($_POST['mensaje']    ?? '');

if (!$padre || !$estudiante || !$telefono || !$email || !$nivel || !$grado) {
    http_response_code(400);
    echo "Faltan campos requeridos";
    exit;
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO solicitudes (padre, estudiante, telefono, email, nivel, grado, mensaje)
         VALUES (:padre, :estudiante, :telefono, :email, :nivel, :grado, :mensaje)"
    );
    $stmt->execute([
        ':padre'      => $padre,
        ':estudiante' => $estudiante,
        ':telefono'   => $telefono,
        ':email'      => $email,
        ':nivel'      => $nivel,
        ':grado'      => $grado,
        ':mensaje'    => $mensaje ?: null,
    ]);
    echo "Solicitud enviada correctamente";
} catch (PDOException $e) {
    http_response_code(500);
    echo "Error al guardar";
}
