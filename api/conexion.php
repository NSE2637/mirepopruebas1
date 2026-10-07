<?php

// ==========================================
// CONEXIÓN A POSTGRESQL - NEON
// ==========================================
// Las credenciales se leen de la variable de entorno DATABASE_URL
// (la crea automáticamente la integración de Neon en Vercel).
// Nunca escribas usuario/contraseña directamente en el código.

date_default_timezone_set("America/Bogota");

$url = getenv("DATABASE_URL");

if (!$url) {
    die("Error de conexión: falta la variable de entorno DATABASE_URL.");
}

$partes = parse_url($url);

$host = $partes["host"];
$puerto = $partes["port"] ?? 5432;
$base_datos = ltrim($partes["path"], "/");
$usuario = urldecode($partes["user"]);
$contraseña = urldecode($partes["pass"]);

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

    error_log($e->getMessage());
    die("Error de conexión con la base de datos.");

}

?>
