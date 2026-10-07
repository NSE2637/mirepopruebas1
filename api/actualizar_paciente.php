<?php
require_once "conexion.php";
$id = (int)(
    $_POST["id"] ?? 0
);
$nombre = trim(
    $_POST["nombre"] ?? ""
);

// Solo permite nombres con letras, espacios, apóstrofes y guiones.
if (!preg_match("/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+(?:[ '-][A-Za-zÁÉÍÓÚÜÑáéíóúüñ]+)*$/u", $nombre)) {
    die("El nombre solo puede contener letras, espacios, apóstrofes o guiones.");
}

// Guarda el nombre en MAYÚSCULAS sin perder las tildes.
$nombre = preg_replace('/\s+/', ' ', $nombre);
$nombre = strtoupper(strtr($nombre, [
    'á' => 'Á', 'é' => 'É', 'í' => 'Í', 'ó' => 'Ó', 'ú' => 'Ú',
    'ü' => 'Ü', 'ñ' => 'Ñ'
]));
$ubicacion = $_POST["ubicacion"] ?? "";
$ubicaciones_validas = [
    "Recepción",
    "Sala de preparación",
    "Cirugía",
    "Sala de recuperación"
];
if (
    $id <= 0 ||
    $nombre == "" ||
    !in_array(
        $ubicacion,
        $ubicaciones_validas
    )
) {
    die("Datos inválidos.");
}
$sql = "UPDATE pacientes
        SET nombre = ?,
            ubicacion = ?

        WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->execute([$nombre, $ubicacion, $id]);
header(
    "Location: admin.php"
);
exit;

?>