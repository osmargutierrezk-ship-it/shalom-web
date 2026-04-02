<?php
$conexion = new mysqli("localhost", "root", "", "formulario_db");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$padre = $_POST['padre'];
$estudiante = $_POST['estudiante'];
$telefono = $_POST['telefono'];
$email = $_POST['email'];
$nivel = $_POST['nivel'];
$grado = $_POST['grado'];
$mensaje = $_POST['mensaje'];

$sql = "INSERT INTO solicitudes (padre, estudiante, telefono, email, nivel, grado, mensaje) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("sssssss", $padre, $estudiante, $telefono, $email, $nivel, $grado, $mensaje);

if ($stmt->execute()) {
    echo "Solicitud enviada correctamente";
} else {
    echo "Error al guardar";
}

$stmt->close();
$conexion->close();
?>