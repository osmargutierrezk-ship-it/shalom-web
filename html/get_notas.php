<?php
/**
 * get_notas.php
 * Devuelve las notas de un estudiante por bimestre.
 * Parámetros GET: bimestre (1–4), ciclo (opcional, default 2026)
 * El código del estudiante se toma de la sesión.
 */
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

session_start();

$codigo   = $_SESSION['codigo']  ?? '';
$bimestre = (int)($_GET['bimestre'] ?? 1);
$ciclo    = trim($_GET['ciclo']    ?? '2026');

if (!$codigo) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Sesión no válida']);
    exit;
}
if ($bimestre < 1 || $bimestre > 4) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Bimestre inválido (1–4)']);
    exit;
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT
             materia,
             COALESCE(zona1,  0)::numeric(5,2) AS zona1,
             COALESCE(zona2,  0)::numeric(5,2) AS zona2,
             COALESCE(zona3,  0)::numeric(5,2) AS zona3,
             zona1 AS zona1_raw, zona2 AS zona2_raw,
             zona3 AS zona3_raw, examen,
             COALESCE(nota_final, 0)::numeric(5,2) AS nota_final,
             nombre_estudiante
         FROM notas
         WHERE codigo_estudiante = :cod
           AND bimestre          = :bim
           AND ciclo_escolar     = :ciclo
         ORDER BY materia"
    );
    $stmt->execute([':cod' => $codigo, ':bim' => $bimestre, ':ciclo' => $ciclo]);
    $rows = $stmt->fetchAll();

    // Calcular promedio general del bimestre
    $promedio = 0;
    if (count($rows)) {
        $promedio = round(array_sum(array_column($rows, 'nota_final')) / count($rows), 2);
    }

    echo json_encode([
        'success'   => true,
        'bimestre'  => $bimestre,
        'promedio'  => $promedio,
        'data'      => $rows
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}