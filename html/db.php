<?php
/**
 * db.php — Conexión centralizada a PostgreSQL usando PDO
 * Usa la variable de entorno DATABASE definida en Render.
 */

function getDB() {
    $databaseUrl = getenv("DATABASE");

    if (!$databaseUrl) {
        http_response_code(500);
        die(json_encode(['error' => 'No se encontró la variable DATABASE']));
    }

    try {
        $pdo = new PDO($databaseUrl);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
    }
}
