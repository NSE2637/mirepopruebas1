<?php
require_once "auth.php"; exigir_login();

require_once "conexion.php";
$ubicaciones = [
    "Sala de preparación",
    "Cirugía",
    "Sala de recuperación"
];
$id = (int)(
    $_GET["id"] ?? 0
);

$stmt = $conexion->prepare(
    "SELECT * FROM pacientes WHERE id = ?"
);
$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();
$paciente = $stmt
    ->get_result()
    ->fetch_assoc();
if (!$paciente) {
    die(
        "Paciente no encontrado."
    );
}
$indiceUbicacion = array_search($paciente["ubicacion"], $ubicaciones, true);
if ($indiceUbicacion === false) {
    die("La ubicación actual del paciente no es válida.");
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar ubicación del paciente</title>
    <link rel="stylesheet"  href = "css/style.css">
<style>
.actualizar-wrap{max-width:760px;margin:30px auto;padding:0 18px}
.actualizar-card{background:#fff;border:1px solid #d9e7eb;border-top:6px solid #0796b5;border-radius:18px;padding:28px;box-shadow:0 10px 30px #063b7318}
.actualizar-card h2{color:#063b73;margin-bottom:8px;text-align:center}
.actualizar-subtitulo{text-align:center;color:#64748b;margin-bottom:22px}
.actualizar-paciente{background:#eef9fb;border:1px solid #c8edf3;padding:14px;border-radius:10px;margin-bottom:20px}
.actualizar-ayuda{font-size:14px;color:#64748b;margin:-8px 0 18px}
.actualizar-acciones{display:flex;gap:12px;flex-wrap:wrap;justify-content:flex-end;margin-top:12px}
@media(max-width:600px){.actualizar-card{padding:20px}.actualizar-acciones>*{width:100%;text-align:center}}
</style>
</head>
<body>
<header>
    <div class="header-contenido">
        <img src="img/logo.png" alt="Logo" class="logo">
    <h1> Sistema de Gestión de Pacientes</h1>
    </div>
</header>
<main class="actualizar-wrap">
    <section class="actualizar-card">
        <h2>Actualizar ubicación del paciente</h2>
        <p class="actualizar-subtitulo">Selecciona la siguiente etapa del proceso para guardar la actualización.</p>
        <div class="actualizar-paciente">
            <strong>Paciente:</strong> <?= htmlspecialchars($paciente["nombre"], ENT_QUOTES, "UTF-8") ?><br>
            <strong>Ubicación actual:</strong> <?= htmlspecialchars($paciente["ubicacion"], ENT_QUOTES, "UTF-8") ?>
        </div>
        <form action="actualizar_paciente.php" method="POST">
            <input type="hidden" name="id" value="<?= (int)$paciente["id"] ?>">
            <label for="ubicacion">Nueva ubicación</label>
            <select id="ubicacion" name="ubicacion" required>
                <?php for ($i = $indiceUbicacion; $i <= min($indiceUbicacion + 1, count($ubicaciones) - 1); $i++): ?>
                    <option value="<?= htmlspecialchars($ubicaciones[$i], ENT_QUOTES, "UTF-8") ?>" <?= $ubicaciones[$i] === $paciente["ubicacion"] ? "selected" : "" ?>>
                        <?= htmlspecialchars($ubicaciones[$i], ENT_QUOTES, "UTF-8") ?><?= $i === $indiceUbicacion ? " (actual)" : "" ?>
                    </option>
                <?php endfor; ?>
            </select>
            <p class="actualizar-ayuda">El sistema permite mantener la ubicación actual o avanzar únicamente a la siguiente sala. No se pueden saltar etapas.</p>
            <div class="actualizar-acciones">
                <a class="boton gris" href="admin.php">Cancelar</a>
                <button class="boton" type="submit">Guardar actualización</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>