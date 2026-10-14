<?php
require_once "auth.php"; exigir_login();

require_once "conexion.php";
$nombre = trim($_POST["nombre"] ?? "");

// Solo permite nombres con letras, espacios, apóstrofes y guiones.
if (!preg_match("/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+(?:[ '-][A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+)*$/u", $nombre)) {
    die("El nombre solo puede contener letras, espacios, apóstrofes o guiones. <a href='crear_paciente.php'>Volver</a>");
}

// Guarda el nombre en MAYÚSCULAS sin perder las tildes.
$nombre = preg_replace('/\s+/', ' ', $nombre);
$nombre = strtoupper(strtr($nombre, [
    'á' => 'Á', 'é' => 'É', 'í' => 'Í', 'ó' => 'Ó', 'ú' => 'Ú',
    'ü' => 'Ü', 'ñ' => 'Ñ'
]));
if ($nombre == "") {
    die(
    "Datos inválidos.<a href='crear_paciente.php'>Volver</a>"
    );
}
$ubicacion = "Sala de preparación";
$sql = "INSERT INTO pacientes (nombre, ubicacion) VALUES (?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
    "ss",
    $nombre,
    $ubicacion
);
if ($stmt->execute()) {
    header(
        "Location: admin.php"
    );
    exit;
}
?>