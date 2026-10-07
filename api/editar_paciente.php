<?php
require_once "conexion.php";
$id = (int)(
    $_GET["id"] ?? 0
);

$stmt = $conexion->prepare(
    "SELECT * FROM pacientes WHERE id = ?"
);
$stmt->execute([$id]);
$paciente = $stmt->fetch();
if (!$paciente) {
    die(
        "Paciente no encontrado."
    );
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar paciente</title>
    <link rel="stylesheet"  href = "css/style.css">
</head>
<body>
<header>
    <div class="header-contenido">
        <img src="img/logo.png" alt="Logo" class="logo">
    <h1> Sistema de Gestión de Pacientes</h1>
    </div>
</header>
<main class="form">
    <h2> Editar paciente </h2>
    <form
        action="actualizar_paciente.php"
        method="POST">
        <input   type="hidden"  name="id" value="<?= $paciente["id"] ?>" >
        <label> Nombre completo </label>
        <input class="nombre-entrada" type="text" name="nombre" required maxlength="100" pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+([ '-][A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+)*" title="Escribe el nombre con letras, espacios, tildes, guiones o apóstrofes." value="<?= htmlspecialchars($paciente["nombre"]) ?>">
       <label>Ubicación actual </label>
        
        <select name="ubicacion">
            <option  value="Recepción"<?= $paciente["ubicacion"] == "Recepción" ? "selected" : "" ?>> Recepción</option>
            <option  value="Sala de preparación"<?= $paciente["ubicacion"] == "Sala de preparación"  ? "selected" : "" ?> >  Sala de preparación </option>
            <option  value="Cirugía"  <?= $paciente["ubicacion"] == "Cirugía"  ? "selected"  : "" ?>>Cirugía</option>
            <option  value="Sala de recuperación"<?= $paciente["ubicacion"] == "Sala de recuperación"  ? "selected" : "" ?> >  Sala de recuperación </option>
           
        </select>
        <br>
        <a class="boton gris"href="pacientes.php" >Cancelar</a>
        <button class="boton"  type="submit"> Actualizar </button>
    </form>
</main>
</body>
</html>