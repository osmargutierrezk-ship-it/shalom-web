<?php
/**
 * get_biblioteca.php
 * Devuelve los libros activos de la biblioteca filtrados por grado del estudiante.
 * Parámetro GET: grado (opcional, si no se pasa usa la sesión)
 */
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

session_start();

$grado = trim($_GET['grado'] ?? ($_SESSION['grado'] ?? ''));

try {
    $pdo = getDB();

    // Traer libros del grado del estudiante, o los que no tienen grado asignado
    if ($grado) {
        $stmt = $pdo->prepare(
            "SELECT id, titulo, materia, grado, descripcion,
                    archivo_nombre, archivo_ruta, ciclo_escolar, activo
             FROM biblioteca
             WHERE activo = true
               AND (
                   grado = :grado
                   OR grado IS NULL
                   OR grado = ''
               )
             ORDER BY materia ASC, titulo ASC"
        );
        $stmt->execute([':grado' => $grado]);
    } else {
        $stmt = $pdo->query(
            "SELECT id, titulo, materia, grado, descripcion,
                    archivo_nombre, archivo_ruta, ciclo_escolar, activo
             FROM biblioteca
             WHERE activo = true
             ORDER BY materia ASC, titulo ASC"
        );
    }

    $rows = $stmt->fetchAll();

    // Construir ruta pública del PDF: biblioteca/archivo_nombre
    foreach ($rows as &$row) {
        if (!empty($row['archivo_nombre'])) {
            $row['pdf_url'] = 'biblioteca/' . rawurlencode($row['archivo_nombre']);
        } else {
            $row['pdf_url'] = null;
        }
    }
    unset($row);

    echo json_encode(['success' => true, 'data' => $rows]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}