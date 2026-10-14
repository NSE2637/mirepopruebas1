<?php
require_once 'conexion.php';
require_once 'auth.php';
if (usuario_autenticado()) { header('Location: ' . ($_SESSION['usuario_rol'] === 'master' ? 'usuarios.php' : 'admin.php')); exit; }
$total = (int)($conexion->query("SELECT COUNT(*) AS total FROM usuarios_sistema")->fetch_assoc()['total'] ?? 0);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    if ($total === 0) {
        $nombre = trim($_POST['nombre'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $clave = $_POST['clave'] ?? '';
        if ($nombre === '' || !preg_match('/^[A-Za-z0-9_.-]{3,40}$/', $usuario) || strlen($clave) < 10) {
            $error = 'Completa el nombre, usa un usuario de 3 a 40 caracteres y una contraseña de mínimo 10 caracteres.';
        } else {
            $hash = password_hash($clave, PASSWORD_DEFAULT);
            $stmt = $conexion->prepare("INSERT INTO usuarios_sistema (nombre, usuario, clave_hash, rol) VALUES (?, ?, ?, 'master')");
            $stmt->bind_param('sss', $nombre, $usuario, $hash);
            if ($stmt->execute()) {
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = $stmt->insert_id; $_SESSION['usuario_nombre'] = $nombre; $_SESSION['usuario_rol'] = 'master';
                header('Location: usuarios.php'); exit;
            } else $error = 'No se pudo crear el Master. El nombre de usuario puede estar ocupado.';
        }
    } else {
        $usuario = trim($_POST['usuario'] ?? ''); $clave = $_POST['clave'] ?? '';
        $stmt = $conexion->prepare('SELECT id, nombre, clave_hash, rol FROM usuarios_sistema WHERE usuario = ? LIMIT 1');
        $stmt->bind_param('s', $usuario); $stmt->execute(); $fila = $stmt->get_result()->fetch_assoc();
        if ($fila && password_verify($clave, $fila['clave_hash'])) {
            session_regenerate_id(true);
            $_SESSION['usuario_id'] = (int)$fila['id']; $_SESSION['usuario_nombre'] = $fila['nombre']; $_SESSION['usuario_rol'] = $fila['rol'];
            header('Location: ' . ($fila['rol'] === 'master' ? 'usuarios.php' : 'admin.php')); exit;
        }
        $error = 'Usuario o contraseña incorrectos.';
    }
}
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $total === 0 ? 'Configurar Master' : 'Iniciar sesión' ?></title><link rel="stylesheet" href="css/style.css"><style>
body.login-page{margin:0;background:linear-gradient(135deg,#063b73 0%,#0796b5 100%);min-height:100vh}
.login-wrap{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:linear-gradient(135deg,rgba(6,59,115,.94),rgba(7,150,181,.88))}
.login-card{background:#fff;width:100%;max-width:440px;padding:34px;border-radius:20px;box-shadow:0 18px 48px #032b4b55;border-top:6px solid #0796b5}
.login-brand{text-align:center;margin-bottom:18px}.login-brand img{width:96px;height:96px;object-fit:contain;border-radius:50%;padding:7px;background:#fff;border:2px solid #d9e7eb;box-shadow:0 4px 14px #063b7320}
.login-card h1{font-size:25px;margin:12px 0 8px;color:#063b73;text-align:center}.login-card p{color:#64748b;text-align:center}.login-card label{display:block;margin:16px 0 6px;font-weight:700;color:#063b73}.login-card input{width:100%;box-sizing:border-box;padding:12px;border:1px solid #cbd5e1;border-radius:8px}.login-card input:focus{outline:2px solid #0796b5;border-color:#0796b5}.login-card button{width:100%;margin-top:22px;padding:13px;border:0;border-radius:8px;background:linear-gradient(135deg,#063b73,#0796b5);color:white;font-weight:bold;cursor:pointer}.login-card button:hover{filter:brightness(.94)}.login-error{background:#fee2e2;color:#991b1b;padding:10px;border-radius:8px}
</style></head><body class="login-page"><div class="login-wrap"><main class="login-card"><div class="login-brand"><img src="img/logo.png" alt="Logo del sistema"></div><h1><?= $total === 0 ? 'Crear cuenta Master' : 'Sistema de Gestión de Pacientes' ?></h1><p><?= $total === 0 ? 'Configuración inicial: crea la única cuenta con control de usuarios.' : 'Inicia sesión para administrar el sistema.' ?></p><?php if ($error): ?><div class="login-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><form method="POST"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(token_csrf()) ?>"><?php if ($total === 0): ?><label>Nombre del administrador Master</label><input name="nombre" required maxlength="100" autocomplete="name"><label>Usuario</label><input name="usuario" required minlength="3" maxlength="40" pattern="[A-Za-z0-9_.-]+" autocomplete="username"><label>Contraseña (mínimo 10 caracteres)</label><input type="password" name="clave" required minlength="10" autocomplete="new-password"><button type="submit">Crear Master</button><?php else: ?><label>Usuario</label><input name="usuario" required autocomplete="username"><label>Contraseña</label><input type="password" name="clave" required autocomplete="current-password"><button type="submit">Ingresar</button><?php endif; ?></form></main></div></body></html>