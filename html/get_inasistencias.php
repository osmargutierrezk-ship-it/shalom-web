<?php
/**
 * get_inasistencias.php
 * Devuelve el historial de inasistencias del estudiante en sesión.
 */
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

session_start();

$codigo = $_SESSION['codigo'] ?? '';
if (!$codigo) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Sesión no válida']);
    exit;
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT
             id,
             TO_CHAR(fecha_ausencia, 'DD/MM/YYYY') AS fecha_fmt,
             TO_CHAR(fecha_ausencia, 'Day')         AS dia_semana,
             tipo, materias_afectadas, motivo,
             justificante_nombre, estado,
             TO_CHAR(created_at, 'DD/MM/YYYY')      AS registrado
         FROM inasistencias
         WHERE codigo_estudiante = :cod
         ORDER BY fecha_ausencia DESC
         LIMIT 30"
    );
    $stmt->execute([':cod' => $codigo]);
    $rows = $stmt->fetchAll();

    // Contadores por estado
    $conteo = ['aprobada' => 0, 'pendiente' => 0, 'rechazada' => 0];
    foreach ($rows as $r) {
        $e = strtolower($r['estado']);
        if (isset($conteo[$e])) $conteo[$e]++;
    }

    echo json_encode(['success' => true, 'data' => $rows, 'conteo' => $conteo]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}