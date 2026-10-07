<?php

// ==========================================
// CONEXIÓN A POSTGRESQL - NEON
// ==========================================

$host = "ep-billowing-unit-b8v9iyl3-pooler.c-14.us-east-1.aws.neon.tech";
$puerto = "5432";
$base_datos = "neondb";
$usuario = "neondb_owner";
$contraseña = "npg_4RqweBfsVnC9";

try {

    $conexion = new PDO(
        "pgsql:host=$host;port=$puerto;dbname=$base_datos;sslmode=require",
        $usuario,
        $contraseña
    );

    // Mostrar errores de PostgreSQL como excepciones
    $conexion->setAttribute(
        PDO::ATTR_ERRMODE,
        PDO::ERRMODE_EXCEPTION
    );

    // Devolver los resultados como arrays asociativos
    $conexion->setAttribute(
        PDO::ATTR_DEFAULT_FETCH_MODE,
        PDO::FETCH_ASSOC
    );

} catch (PDOException $e) {

    die("Error de conexión con la base de datos: " . $e->getMessage());

}

?>
