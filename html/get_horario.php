<?php
/**
 * get_horario.php
 * Devuelve el horario de un grado/sección en JSON.
 * Parámetros GET: grado, seccion
 * Ejemplo: /get_horario.php?grado=4to+Básico&seccion=A
 */
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

session_start();

// Leer parámetros (primero GET, luego sesión)
$grado   = trim($_GET['grado']   ?? ($_SESSION['grado']   ?? ''));
$seccion = trim($_GET['seccion'] ?? ($_SESSION['seccion'] ?? ''));

if (!$grado || !$seccion) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Se requiere grado y seccion']);
    exit;
}

try {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "SELECT
             id, grado, seccion, dia,
             TO_CHAR(hora_inicio, 'HH24:MI') AS hora_inicio,
             TO_CHAR(hora_fin,    'HH24:MI') AS hora_fin,
             materia, profesor, aula
         FROM horarios
         WHERE grado = :grado AND seccion = :sec
         ORDER BY
             CASE dia
                 WHEN 'Lunes'     THEN 1
                 WHEN 'Martes'    THEN 2
                 WHEN 'Miércoles' THEN 3
                 WHEN 'Jueves'    THEN 4
                 WHEN 'Viernes'   THEN 5
                 ELSE 6
             END,
             hora_inicio ASC"
    );
    $stmt->execute([':grado' => $grado, ':sec' => $seccion]);
    $rows = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}