<?php
$host = "localhost";
$user = "root";
$password = ""; // Ajusta si tienes contraseña
$database = "formulario_db";

// Conexión
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener todas las tablas
$tables = [];
$result = $conn->query("SHOW TABLES");
while ($row = $result->fetch_array()) {
    $tables[] = $row[0];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard-CBS</title>
    <link rel="icon" type="image/png" href="logo_cbs.png">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #0a3d62, #3c6382);
            color: #fff;
            margin: 0;
            padding: 0;
        }

        header {
            background: #1e3799;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            animation: fadeInDown 1.2s ease;
        }

        header h1 {
            margin: 0;
            font-size: 2.5em;
            color: #ffffff;
            letter-spacing: 2px;
        }

        .container {
            padding: 30px;
            display: grid;
            grid-template-columns: 1fr;
            gap: 40px;
            animation: fadeInUp 1.5s ease;
        }

        .table-container {
            background: rgba(255,255,255,0.1);
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 6px 15px rgba(0,0,0,0.4);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .table-container:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.6);
        }

        h2 {
            color: #f1c40f;
            margin-bottom: 15px;
            text-transform: uppercase;
            font-size: 1.4em;
            border-bottom: 2px solid #f1c40f;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(255,255,255,0.15);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        th {
            background: #0a3d62;
            color: #f1c40f;
        }

        tr:hover {
            background: rgba(255,255,255,0.2);
            transition: background 0.3s ease;
        }

        /* Animaciones */
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        footer {
            text-align: center;
            padding: 15px;
            background: #1e3799;
            color: #fff;
            margin-top: 40px;
            font-size: 0.9em;
            animation: fadeInUp 2s ease;
        }
    </style>
</head>
<body>
    <header>
        <h1>Dashboard Colegio Bautista Shalom</h1>
    </header>

    <div class="container">
        <?php foreach ($tables as $table): ?>
            <div class="table-container">
                <h2>Tabla: <?php echo $table; ?></h2>
                <table>
                    <thead>
                        <tr>
                            <?php
                            $columns = $conn->query("SHOW COLUMNS FROM $table");
                            while ($col = $columns->fetch_assoc()) {
                                echo "<th>{$col['Field']}</th>";
                            }
                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $rows = $conn->query("SELECT * FROM $table");
                        while ($row = $rows->fetch_assoc()) {
                            echo "<tr>";
                            foreach ($row as $value) {
                                echo "<td>" . htmlspecialchars($value) . "</td>";
                            }
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    </div>

    <footer>
        © 2026 Colegio Bautista Shalom - Guatemala, C.A.
    </footer>
</body>
</html>
