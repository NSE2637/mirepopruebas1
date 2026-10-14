<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'samesite' => 'Lax'
    ]);
    session_start();
}
function usuario_autenticado(): bool {
    return isset($_SESSION['usuario_id'], $_SESSION['usuario_nombre'], $_SESSION['usuario_rol']);
}
function exigir_login(): void {
    if (!usuario_autenticado()) {
        header('Location: login.php');
        exit;
    }
}
function exigir_master(): void {
    exigir_login();
    if ($_SESSION['usuario_rol'] !== 'master') {
        http_response_code(403);
        exit('Acceso denegado. Solo el usuario Master puede administrar cuentas. <a href="admin.php">Volver</a>');
    }
}
function token_csrf(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function validar_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
        http_response_code(403);
        exit('Solicitud no válida. Recarga la página e inténtalo de nuevo.');
    }
}
?>