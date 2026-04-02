<?php
session_start();

// Conexión a la base de datos
$conn = new mysqli("localhost", "root", "", "formulario_db");

if ($conn->connect_error) {
    die("Error de conexión");
}

// Recibir datos del formulario
$correo = $_POST['email'];
$password = $_POST['password'];

// Validar que no vengan vacíos
if (empty($correo) || empty($password)) {
    echo "Completa todos los campos";
    exit;
}

// Buscar usuario por correo
$stmt = $conn->prepare("SELECT nombre, password, codigo FROM usuarios WHERE correo = ?");
$stmt->bind_param("s", $correo);
$stmt->execute();
$result = $stmt->get_result();

// Verificar si existe el usuario
if ($result->num_rows === 0) {
    echo "Usuario no encontrado";
    exit;
}

$user = $result->fetch_assoc();

// Verificar contraseña
if (password_verify($password, $user['password'])) {

    // Guardar datos en sesión
    $_SESSION['usuario'] = $user['nombre'];
    $_SESSION['codigo'] = $user['codigo'];

    echo "Login exitoso";

} else {
    echo "Contraseña incorrecta";
}

$stmt->close();
$conn->close();
?>