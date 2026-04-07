<?php
/**
 * get_classroom.php
 * Devuelve las clases activas filtradas por grado y sección.
 */
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

session_start();

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
        "SELECT id, materia, profesor, link_classroom, link_meet,
                tipo_meet, icono, color_banner
         FROM classroom
         WHERE grado = :grado AND seccion = :sec AND activo = true
         ORDER BY materia ASC"
    );
    $stmt->execute([':grado' => $grado, ':sec' => $seccion]);
    $rows = $stmt->fetchAll();

    echo json_encode(['success' => true, 'data' => $rows]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}