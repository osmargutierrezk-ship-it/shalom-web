<?php
/**
 * db.php — Conexión centralizada a PostgreSQL
 * Usa la variable de entorno DATABASE definida en Render.
 */

function getDB() {
    $databaseUrl = getenv("DATABASE");

    if (!$databaseUrl) {
        http_response_code(500);
        die(json_encode(['error' => 'No se encontró la variable DATABASE']));
    }

    // Conexión directa con pg_connect usando la URL completa
    $conn = pg_connect($databaseUrl);

    if (!$conn) {
        http_response_code(500);
        die(json_encode(['error' => 'Error de conexión a la base de datos']));
    }

    return $conn;
}
