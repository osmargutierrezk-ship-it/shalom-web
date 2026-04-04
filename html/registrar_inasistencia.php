<?php
/**
 * registrar_inasistencia.php
 * Recibe un POST con los datos del formulario de inasistencia,
 * valida, sube el justificante (si existe) y guarda en la BD.
 *
 * Campos POST esperados:
 *   fecha     (string YYYY-MM-DD)  — obligatorio
 *   tipo      (string)             — obligatorio
 *   materias  (string)             — obligatorio
 *   motivo    (string)             — obligatorio
 *   justificante (FILE, opcional)
 */
header('Content-Type: application/json; charset=utf-8');
require_once 'db.php';

session_start();

/* ── Sólo POST ─────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

/* ── Sesión activa ─────────────────────── */
$codigo = $_SESSION['codigo'] ?? '';
$nombre = $_SESSION['nombre'] ?? '';
if (!$codigo) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Sesión no válida. Inicia sesión nuevamente.']);
    exit;
}

/* ── Leer y validar campos ─────────────── */
$fecha    = trim($_POST['fecha']    ?? '');
$tipo     = trim($_POST['tipo']     ?? '');
$materias = trim($_POST['materias'] ?? '');
$motivo   = trim($_POST['motivo']   ?? '');

$errores = [];
if (!$fecha)    $errores[] = 'La fecha de ausencia es obligatoria.';
if (!$tipo)     $errores[] = 'El tipo de inasistencia es obligatorio.';
if (!$materias) $errores[] = 'Selecciona al menos una materia afectada.';
if (!$motivo)   $errores[] = 'El motivo es obligatorio.';

// Validar que fecha sea válida y no futura (máx. +7 días)
if ($fecha) {
    $fechaObj = DateTime::createFromFormat('Y-m-d', $fecha);
    if (!$fechaObj) {
        $errores[] = 'Formato de fecha inválido.';
    } else {
        $limite = new DateTime('+7 days');
        if ($fechaObj > $limite) {
            $errores[] = 'No se puede registrar una inasistencia con más de 7 días de anticipación.';
        }
    }
}

if ($errores) {
    http_response_code(422);
    echo json_encode(['success' => false, 'error' => implode(' ', $errores)]);
    exit;
}

/* ── Justificante (archivo opcional) ───── */
$justificanteNombre = null;

if (isset($_FILES['justificante']) && $_FILES['justificante']['error'] !== UPLOAD_ERR_NO_FILE) {
    $file = $_FILES['justificante'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Error al subir el archivo (código ' . $file['error'] . ').']);
        exit;
    }

    // Validar tipo MIME real (no confiar en la extensión del cliente)
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeReal = $finfo->file($file['tmp_name']);
    $allowed  = ['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'];
    if (!in_array($mimeReal, $allowed)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Solo se permiten archivos PDF, JPG o PNG.']);
        exit;
    }

    // Validar tamaño (máx 5 MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'El archivo no debe superar 5 MB.']);
        exit;
    }

    // Guardar con nombre seguro
    $ext       = match($mimeReal) {
        'application/pdf' => 'pdf',
        'image/jpeg','image/jpg' => 'jpg',
        'image/png'  => 'png',
        default      => 'bin'
    };
    $uploadDir = __DIR__ . '/uploads/justificantes/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $safeName = preg_replace('/[^A-Za-z0-9_-]/', '_', $codigo)
              . '_' . date('Ymd_His')
              . '.' . $ext;

    if (!move_uploaded_file($file['tmp_name'], $uploadDir . $safeName)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'No se pudo guardar el justificante en el servidor.']);
        exit;
    }
    $justificanteNombre = $safeName;
}

/* ── Insertar en la BD ─────────────────── */
try {
    $pdo  = getDB();
    $stmt = $pdo->prepare(
        "INSERT INTO inasistencias
             (codigo_estudiante, nombre_estudiante, fecha_ausencia, tipo,
              materias_afectadas, motivo, justificante_nombre, estado)
         VALUES
             (:cod, :nom, :fecha, :tipo, :materias, :motivo, :just, 'pendiente')"
    );
    $stmt->execute([
        ':cod'      => $codigo,
        ':nom'      => $nombre,
        ':fecha'    => $fecha,
        ':tipo'     => $tipo,
        ':materias' => $materias,
        ':motivo'   => $motivo,
        ':just'     => $justificanteNombre,
    ]);

    echo json_encode([
        'success' => true,
        'mensaje' => 'Solicitud registrada correctamente. El estado inicial es Pendiente.',
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error de base de datos: ' . $e->getMessage()]);
}