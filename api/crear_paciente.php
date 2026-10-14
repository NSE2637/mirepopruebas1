<?php require_once "auth.php"; exigir_login(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Nuevo paciente</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header>

    <div class="header-contenido">
        <img src="img/logo.png" alt="Logo" class="logo">
    <h1> Sistema de Gestión de Pacientes</h1>
    </div>
</header>
<main class="form">
    <h2>  Registrar paciente</h2>
    <form action="guardar_paciente.php" method="POST">
        <label>Nombre completo</label>
        <input class="nombre-entrada" type="text" name="nombre" required maxlength="100" pattern="[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+([ '-][A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+)*" title="Escribe el nombre con letras, espacios, tildes, guiones o apóstrofes.">
        <label>Ubicación inicial</label>
        <p>Sala de preparación</p>
        <br>
        <a class="boton gris" href="pacientes.php"> Cancelar</a>
        <button class="boton" type="submit">Guardar paciente</button>
    </form>
</main>
</body>
</html>