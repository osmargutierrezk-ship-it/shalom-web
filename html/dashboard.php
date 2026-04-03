<?php
session_start();
require_once 'db.php';

$pdo = getDB();

// Obtener todas las tablas del schema public de PostgreSQL
$tablesStmt = $pdo->query(
    "SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename"
);
$tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — CBS</title>
    <link rel="icon" type="image/png" href="logo_cbs.png">
    <style>
        body { font-family:'Segoe UI',sans-serif; background:linear-gradient(135deg,#0a3d62,#3c6382); color:#fff; margin:0; padding:0; }
        header { background:#1e3799; padding:20px; text-align:center; box-shadow:0 4px 10px rgba(0,0,0,.3); animation:fadeInDown 1.2s ease; }
        header h1 { margin:0; font-size:2.5em; color:#fff; letter-spacing:2px; }
        .container { padding:30px; display:grid; grid-template-columns:1fr; gap:40px; animation:fadeInUp 1.5s ease; }
        .table-container { background:rgba(255,255,255,.1); border-radius:12px; padding:20px; box-shadow:0 6px 15px rgba(0,0,0,.4); transition:transform .3s,box-shadow .3s; }
        .table-container:hover { transform:translateY(-8px); box-shadow:0 10px 20px rgba(0,0,0,.6); }
        h2 { color:#f1c40f; margin-bottom:15px; text-transform:uppercase; font-size:1.4em; border-bottom:2px solid #f1c40f; padding-bottom:5px; }
        table { width:100%; border-collapse:collapse; background:rgba(255,255,255,.15); border-radius:8px; overflow:hidden; }
        th,td { padding:10px; text-align:left; border-bottom:1px solid rgba(255,255,255,.2); word-break:break-word; }
        th { background:#0a3d62; color:#f1c40f; }
        tr:hover { background:rgba(255,255,255,.2); transition:background .3s; }
        .empty { color:rgba(255,255,255,.5); font-style:italic; padding:12px; }
        @keyframes fadeInDown { from{opacity:0;transform:translateY(-30px)} to{opacity:1;transform:none} }
        @keyframes fadeInUp   { from{opacity:0;transform:translateY(30px)}  to{opacity:1;transform:none} }
        footer { text-align:center; padding:15px; background:#1e3799; color:#fff; margin-top:40px; font-size:.9em; }
    </style>
</head>
<body>
<header><h1>Dashboard — Colegio Bautista Shalom</h1></header>
<div class="container">

<?php foreach ($tables as $table): ?>
    <div class="table-container">
        <h2>Tabla: <?= htmlspecialchars($table) ?></h2>

        <?php
        // Obtener columnas
        $colStmt = $pdo->query(
            "SELECT column_name FROM information_schema.columns
             WHERE table_schema = 'public' AND table_name = " . $pdo->quote($table) . "
             ORDER BY ordinal_position"
        );
        $columns = $colStmt->fetchAll(PDO::FETCH_COLUMN);

        // Obtener filas
        $rowStmt = $pdo->query("SELECT * FROM \"$table\"");
        $rows    = $rowStmt->fetchAll();
        ?>

        <table>
            <thead>
                <tr>
                    <?php foreach ($columns as $col): ?>
                        <th><?= htmlspecialchars($col) ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="<?= count($columns) ?>" class="empty">Sin registros</td></tr>
                <?php else: ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <?php foreach ($row as $value): ?>
                                <td><?= htmlspecialchars((string)($value ?? '')) ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endforeach; ?>

</div>
<footer>© 2026 Colegio Bautista Shalom — Guatemala, C.A.</footer>
</body>
</html>
