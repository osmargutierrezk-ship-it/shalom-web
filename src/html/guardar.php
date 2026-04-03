<?php
require_once 'db.php';

$conn = getDB();

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

$sql  = "INSERT INTO solicitudes (padre, estudiante, telefono, email, nivel, grado, mensaje)
         VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssss", $padre, $estudiante, $telefono, $email, $nivel, $grado, $mensaje);

if ($stmt->execute()) {
    echo "Solicitud enviada correctamente";
} else {
    http_response_code(500);
    echo "Error al guardar";
}

$stmt->close();
$conn->close();
