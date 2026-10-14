<?php
require_once "auth.php"; exigir_login();

require_once "conexion.php";
$id = (int)(
    $_POST["id"] ?? 0
);
$ubicacion = $_POST["ubicacion"] ?? "";
$ubicaciones_validas = [
    "Sala de preparación",
    "Cirugía",
    "Sala de recuperación"
];
if (
    $id <= 0 ||
    !in_array(
        $ubicacion,
        $ubicaciones_validas
    )
) {
    die("Datos inválidos.");
}

$stmt = $conexion->prepare(
    "SELECT ubicacion FROM pacientes WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$pacienteActual = $stmt->get_result()->fetch_assoc();
if (!$pacienteActual) {
    die("Paciente no encontrado.");
}
$indiceActual = array_search($pacienteActual["ubicacion"], $ubicaciones_validas, true);
$indiceSolicitado = array_search($ubicacion, $ubicaciones_validas, true);
if (
    $indiceActual === false ||
    $indiceSolicitado === false ||
    $indiceSolicitado < $indiceActual ||
    $indiceSolicitado > $indiceActual + 1
) {
    die("Solo puedes mantener la sala actual o avanzar a la siguiente sala.");
}

$sql = "UPDATE pacientes
        SET ubicacion = ?
        WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param(
    "si",
    $ubicacion,
    $id
);
$stmt->execute();

if (
    $ubicacion !== "Sala de recuperación" ||
    $pacienteActual["ubicacion"] !== "Sala de recuperación"
) {
    $stmt = $conexion->prepare(
        "DELETE FROM dietas_pacientes WHERE paciente_id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header(
    "Location: admin.php"
);
exit;

?>