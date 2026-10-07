<?php
require_once "conexion.php";

$buscar = trim($_GET["buscar"] ?? "");
$ubicacion = $_GET["ubicacion"] ?? "";

$sql = "SELECT * FROM pacientes WHERE 1=1";
$parametros = [];

if ($buscar !== "") {
    $sql .= " AND nombre ILIKE ?";
    $parametros[] = "%" . $buscar . "%";
}

if ($ubicacion !== "") {
    $sql .= " AND ubicacion = ?";
    $parametros[] = $ubicacion;
}

$sql .= " ORDER BY id DESC";
$stmt = $conexion->prepare($sql);
$stmt->execute($parametros);
$pacientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="panel-admin">
<div class="admin-layout">
    <aside class="sidebar">
        <img src="img/logo.png" alt="Logo" class="logo-sidebar">
        <h2>Sistema Pacientes</h2>
        <nav>
            <a class="activo" href="admin.php">👥 Lista de Pacientes</a>
            <a href="crear_paciente.php">➕ Registrar Paciente</a>
            <a href="pacientes.php" target="_blank">🖥️ Pantalla para pacientes</a>
        </nav>
    </aside>

    <section class="admin-contenido">
        <div class="barra-superior">
            <strong>Panel de Control</strong>
            <span>Sistema de Gestión de Pacientes</span>
        </div>

        <main class="admin-main">
            <div class="admin-titulo">
                <div>
                    <h1>Lista de Pacientes</h1>
                    <p>Gestión y ubicación en tiempo real</p>
                </div>
                <a class="boton verde" href="crear_paciente.php">➕ Nuevo Paciente</a>
            </div>

            <div class="tabla-contenedor">
                <form class="filtros" method="GET" action="admin.php">
                    <input type="text" name="buscar" placeholder="Buscar por nombre..." value="<?= htmlspecialchars($buscar) ?>">
                    <select name="ubicacion">
                        <option value="">Todas las ubicaciones</option>
                        <option value="Recepción" <?= $ubicacion === "Recepción" ? "selected" : "" ?>>Recepción</option>
                        <option value="Sala de preparación" <?= $ubicacion === "Sala de preparación" ? "selected" : "" ?>>Sala de preparación</option>
                        <option value="Cirugía" <?= $ubicacion === "Cirugía" ? "selected" : "" ?>>Cirugía</option>
                        <option value="Sala de recuperación" <?= $ubicacion === "Sala de recuperación" ? "selected" : "" ?>>Sala de recuperación</option>
                    </select>
                    <button class="boton azul" type="submit">🔎 Filtrar</button>
                    <a class="boton limpiar" href="admin.php">Limpiar</a>
                </form>

                <div class="tabla-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>NOMBRE DEL PACIENTE</th>
                                <th>UBICACIÓN</th>
                                <th>FECHA DE REGISTRO</th>
                                <th>ACCIONES</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (count($pacientes) > 0): ?>
                            <?php foreach ($pacientes as $paciente): ?>
                                <tr>
                                    <td>#<?= (int)$paciente["id"] ?></td>
                                    <td><strong class="nombre-paciente"><?= htmlspecialchars($paciente["nombre"]) ?></strong></td>
                                    <td>
                                        <?php
                                        $clase = "badge azul-claro";
                                        $texto = "Recepción";
                                        if ($paciente["ubicacion"] === "Sala de preparación") {
                                            $clase = "badge naranja";
                                            $texto = "Sala de preparación";
                                        } elseif ($paciente["ubicacion"] === "Cirugía") {
                                            $clase = "badge rojo";
                                            $texto = "Cirugía";
                                        } elseif ($paciente["ubicacion"] === "Sala de recuperación") {
                                            $clase = "badge verde-claro";
                                            $texto = "Sala de recuperación";
                                        }
                                        ?>
                                        <span class="<?= $clase ?>"><?= $texto ?></span>
                                    </td>
                                    <td><?= date("d/m/Y H:i", strtotime($paciente["fecha_registro"])) ?></td>
                                    <td class="acciones-tabla">
                                        <a href="editar_paciente.php?id=<?= (int)$paciente["id"] ?>">✏️ Editar</a>
                                        <a class="eliminar" href="eliminar_paciente.php?id=<?= (int)$paciente["id"] ?>" onclick="return confirm('¿Eliminar paciente?')">🗑️ Eliminar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="sin-resultados">No hay pacientes registrados.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </section>
</div>
</body>
</html>
