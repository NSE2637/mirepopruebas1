<?php
require_once "conexion.php";
$id = (int)(
    $_GET["id"] ?? 0
);
if ($id > 0) {
    $stmt = $conexion->prepare(
        "DELETE FROM pacientes WHERE id = ?"
    );
    $stmt->bind_param(
        "i",
        $id
    );
    $stmt->execute();
}
header(
    "Location: admin.php"
);
exit;
?>