<?php
require_once "conexion.php";
$id = (int)(
    $_GET["id"] ?? 0
);
if ($id > 0) {
    $stmt = $conexion->prepare(
        "DELETE FROM pacientes WHERE id = ?"
    );
    $stmt->execute([$id]);
}
header(
    "Location: admin.php"
);
exit;
?>