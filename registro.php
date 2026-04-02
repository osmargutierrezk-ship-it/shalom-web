<?php
$conn = new mysqli("localhost", "root", "", "formulario_db");

if ($conn->connect_error) {
    die("Error de conexión");
}

$nombre = $_POST['nombre'];
$correo = $_POST['correo'];
$telefono = $_POST['telefono'];
$codigo = $_POST['codigo'];
$password = $_POST['password'];

// 1. Verificar código existente
$checkCodigo = $conn->query("SELECT * FROM estudiantes WHERE codigo='$codigo'");
if ($checkCodigo->num_rows == 0) {
    echo "Código estudiantil no válido";
    exit;
}

// 2. Verificar duplicado
$checkUser = $conn->query("SELECT * FROM usuarios WHERE codigo='$codigo'");
if ($checkUser->num_rows > 0) {
    echo "Este código ya está registrado";
    exit;
}

// 3. Encriptar contraseña (MUY IMPORTANTE)
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// 4. Insertar usuario
$sql = "INSERT INTO usuarios (nombre, correo, telefono, password, codigo) 
        VALUES ('$nombre', '$correo', '$telefono', '$passwordHash', '$codigo')";

if ($conn->query($sql)) {
    echo "Registro exitoso";
} else {
    echo "Error al registrar";
}

$conn->close();
?>