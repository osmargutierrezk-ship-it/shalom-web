<?php
/**
 * db.php — Conexión centralizada a PostgreSQL usando PDO
 * Lee la variable de entorno DATABASE definida en Render.
 *
 * Formato esperado:
 *   postgresql://usuario:contraseña@host/nombre_bd
 */
function getDB(): PDO {
    $databaseUrl = getenv('DATABASE');

    if (!$databaseUrl) {
        http_response_code(500);
        die(json_encode(['error' => 'No se encontró la variable de entorno DATABASE']));
    }

    // Convertir URL de PostgreSQL al DSN que espera PDO
    // postgresql://user:pass@host:port/dbname  →  pgsql:host=...;dbname=...
    $url    = parse_url($databaseUrl);
    $host   = $url['host']   ?? 'localhost';
    $port   = $url['port']   ?? 5432;
    $dbname = ltrim($url['path'] ?? '', '/');
    $user   = $url['user']   ?? '';
    $pass   = $url['pass']   ?? '';

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";

    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode(['error' => 'Error de conexión: ' . $e->getMessage()]));
    }
}
