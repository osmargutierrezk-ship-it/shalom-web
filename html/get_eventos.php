<?php
/**
 * get_eventos.php
 * Devuelve los eventos próximos en JSON.
 * Parámetro opcional: ?categoria=examen|deporte|cultural|general
 */
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';
date_default_timezone_set('America/Guatemala');
$categoria = trim($_GET['categoria'] ?? '');

try {
    $pdo = getDB();

    if ($categoria && $categoria !== 'all') {
        $stmt = $pdo->prepare(
            "SELECT * FROM eventos
             WHERE categoria = :cat AND fecha >= CURRENT_DATE
             ORDER BY fecha ASC, hora ASC"
        );
        $stmt->execute([':cat' => $categoria]);
    } else {
        $stmt = $pdo->query(
            "SELECT * FROM eventos
             WHERE fecha >= CURRENT_DATE
             ORDER BY fecha ASC, hora ASC"
        );
    }

    $rows = $stmt->fetchAll();

    // Formatear fecha para JavaScript (ISO 8601)
    foreach ($rows as &$row) {
        $row['fecha_fmt'] = (new DateTime($row['fecha']))->format('d/m/Y');
        $row['dia']       = (new DateTime($row['fecha']))->format('d');
        $row['mes']       = strtoupper((new DateTime($row['fecha']))->format('M'));
        $row['hora_fmt']  = $row['hora'] ? substr($row['hora'], 0, 5) : null;
    }
    unset($row);

    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}