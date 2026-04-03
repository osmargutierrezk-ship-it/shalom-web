<?php
/**
 * db.php — Conexión centralizada a MySQL
 * Lee las credenciales desde variables de entorno (definidas en docker-compose.yml).
 * Si no existen las env vars, usa los valores por defecto para desarrollo local.
 */
function getDB(): mysqli {
    $host = getenv('DB_HOST') ?: 'localhost';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASS') ?: '';
    $name = getenv('DB_NAME') ?: 'formulario_db';

    $conn = new mysqli($host, $user, $pass, $name);
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode(['error' => 'Error de conexión a la base de datos']));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
