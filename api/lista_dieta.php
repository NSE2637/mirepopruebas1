<?php
require_once "auth.php"; exigir_login();

require_once "conexion.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $paciente_id = filter_input(INPUT_POST, "paciente_id", FILTER_VALIDATE_INT);
    $accion = $_POST["accion"] ?? "";

    if (!$paciente_id || !in_array($accion, ["solicitar", "marcar_suministrada"], true)) {
        die("Paciente inválido.");
    }

    $stmt = $conexion->prepare(
        "SELECT id FROM pacientes
         WHERE id = ? AND ubicacion = 'Sala de recuperación'"
    );
    $stmt->bind_param("i", $paciente_id);
    $stmt->execute();

    if ($stmt->get_result()->num_rows === 0) {
        die("Solo se puede suministrar dieta a pacientes en Sala de recuperación.");
    }

    if ($accion === "solicitar") {
        $stmt = $conexion->prepare(
            "INSERT IGNORE INTO dietas_pacientes (paciente_id, dieta, suministrada)
             VALUES (?, 'Requiere dieta', 0)"
        );
        $stmt->bind_param("i", $paciente_id);
        $stmt->execute();
        header("Location: lista_dieta.php?guardado=1");
    } else {
        $stmt = $conexion->prepare(
            "UPDATE dietas_pacientes SET suministrada = 1
             WHERE paciente_id = ? AND suministrada = 0"
        );
        $stmt->bind_param("i", $paciente_id);
        $stmt->execute();
        header("Location: lista_dieta.php?estado_actualizado=1");
    }

    exit;
}

$resultado = $conexion->query(
   "SELECT p.id, p.nombre, d.dieta, d.suministrada, d.fecha_actualizacion
     FROM pacientes p
    LEFT JOIN dietas_pacientes d ON d.paciente_id = p.id
    WHERE p.ubicacion = 'Sala de recuperación'
     ORDER BY p.id DESC"
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Dieta</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="panel-admin">
<div class="admin-layout">
    <aside class="sidebar">
        <img src="img/logo.png" alt="Logo" class="logo-sidebar">
        <h2>Sistema Pacientes</h2>
        <nav>
            <a href="admin.php">👥 Lista de Pacientes</a>
            <a class="activo" href="lista_dieta.php">🍽️ Lista de Dieta</a>
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
                    <h1>Lista de Dieta</h1>
                   <p>Registra qué pacientes en Sala de recuperación necesitan dieta.</p>
                </div>
            </div>

           <?php if (isset($_GET["guardado"])): ?>
               <p class="dieta-mensaje" role="status">Se registró que el paciente necesita dieta.</p>
           <?php endif; ?>
           <?php if (isset($_GET["estado_actualizado"])): ?>
               <p class="dieta-mensaje" role="status">La dieta quedó marcada como suministrada.</p>
           <?php endif; ?>

           <div class="tabla-contenedor">
               <div class="tabla-scroll">
                   <table>
                       <thead>
                           <tr>
                               <th>ID</th>
                               <th>NOMBRE DEL PACIENTE</th>
                               <th>ESTADO</th>
                               <th>REGISTRO</th>
                           </tr>
                       </thead>
                       <tbody>
                       <?php if ($resultado->num_rows > 0): ?>
                           <?php while ($paciente = $resultado->fetch_assoc()): ?>
                               <tr>
                                   <td>#<?= (int)$paciente["id"] ?></td>
                                   <td><strong class="nombre-paciente"><?= htmlspecialchars($paciente["nombre"], ENT_QUOTES, "UTF-8") ?></strong></td>
                                   <td>
                                       <?php if ($paciente["dieta"] === null): ?>
                                           <span class="badge azul-claro">Sin solicitud</span>
                                       <?php elseif ((int)$paciente["suministrada"] === 1): ?>
                                           <span class="badge verde-claro">Ya suministrada</span>
                                       <?php else: ?>
                                           <span class="badge dieta-pendiente">Pendiente</span>
                                       <?php endif; ?>
                                       <?php if ($paciente["dieta"] !== null && (int)$paciente["suministrada"] === 0): ?>
                                           <form class="dieta-estado-form" method="POST" action="lista_dieta.php" onsubmit="return confirm('¿Confirmas que ya se suministró la dieta? Esta acción solo se puede registrar una vez.');">
                                               <input type="hidden" name="paciente_id" value="<?= (int)$paciente["id"] ?>">
                                               <input type="hidden" name="accion" value="marcar_suministrada">
                                               <button class="boton dieta-atendida-boton" type="submit">Marcar como suministrada</button>
                                           </form>
                                       <?php endif; ?>
                                   </td>
                                   <td>
                                       <?php if ($paciente["dieta"] === null): ?>
                                           <form class="dieta-form" method="POST" action="lista_dieta.php">
                                               <input type="hidden" name="paciente_id" value="<?= (int)$paciente["id"] ?>">
                                               <input type="hidden" name="accion" value="solicitar">
                                               <button class="boton dieta-guardar-boton" type="submit">Suministrar dieta</button>
                                           </form>
                                       <?php else: ?>
                                           <span class="badge dieta-registrada">Registrada</span>
                                       <?php endif; ?>
                                   </td>
                               </tr>
                           <?php endwhile; ?>
                       <?php else: ?>
                           <tr><td colspan="4" class="sin-resultados">No hay pacientes en Sala de recuperación.</td></tr>
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
