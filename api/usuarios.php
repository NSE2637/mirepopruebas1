<?php
require_once 'conexion.php'; require_once 'auth.php'; exigir_master();
$mensaje=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
 validar_csrf(); $accion=$_POST['accion']??'';
 if ($accion==='crear') {
  $nombre=trim($_POST['nombre']??''); $usuario=trim($_POST['usuario']??''); $clave=$_POST['clave']??''; $rol='usuario';
  if ($nombre==='' || !preg_match('/^[A-Za-z0-9_.-]{3,40}$/',$usuario) || strlen($clave)<10) $error='Verifica los datos. La contraseña debe tener mínimo 10 caracteres.';
  else { $hash=password_hash($clave,PASSWORD_DEFAULT); $st=$conexion->prepare('INSERT INTO usuarios_sistema (nombre,usuario,clave_hash,rol) VALUES (?,?,?,?)'); $st->bind_param('ssss',$nombre,$usuario,$hash,$rol); if($st->execute()) $mensaje='Usuario creado correctamente.'; else $error='No se pudo crear. Verifica que el usuario no exista.'; }
 } elseif ($accion==='cambiar_clave') {
  $id=(int)($_POST['id']??0); $clave=$_POST['clave']??''; $confirmacion=$_POST['clave_confirmacion']??'';
  if(!is_string($clave) || !is_string($confirmacion) || $id<1 || strlen($clave)<10) $error='La contraseña debe tener mínimo 10 caracteres.';
  elseif($clave!==$confirmacion) $error='La confirmación de contraseña no coincide.';
  else {
   if($id===(int)$_SESSION['usuario_id']) {
    $clave_actual=$_POST['clave_actual']??'';
    $st=$conexion->prepare('SELECT clave_hash FROM usuarios_sistema WHERE id=?'); $st->bind_param('i',$id); $st->execute(); $cuenta=$st->get_result()->fetch_assoc();
    if(!is_string($clave_actual) || !$cuenta || !password_verify($clave_actual,$cuenta['clave_hash'])) $error='La contraseña actual no es correcta.';
   }
   if(!$error) { $hash=password_hash($clave,PASSWORD_DEFAULT); $st=$conexion->prepare('UPDATE usuarios_sistema SET clave_hash=? WHERE id=?'); $st->bind_param('si',$hash,$id); if($st->execute() && $st->affected_rows===1) $mensaje='Contraseña actualizada.'; else $error='No se pudo actualizar la contraseña.'; }
  }
 } elseif ($accion==='eliminar') {
  $id=(int)($_POST['id']??0);
  if($id<1 || $id===(int)$_SESSION['usuario_id']) $error='No puedes eliminar tu propia cuenta.';
  else { $st=$conexion->prepare('DELETE FROM usuarios_sistema WHERE id=? AND rol <> \'master\''); $st->bind_param('i',$id); $st->execute(); if($st->affected_rows) $mensaje='Usuario eliminado.'; else $error='No se eliminó. Las cuentas Master no se pueden borrar desde esta pantalla.'; }
 }
}
$usuarios=$conexion->query('SELECT id,nombre,usuario,rol,creado_en FROM usuarios_sistema ORDER BY id ASC'); $csrf=token_csrf();
?>
<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Administrar usuarios</title><link rel="stylesheet" href="css/style.css"><style>
.um-wrap{max-width:1150px;margin:30px auto;padding:0 18px}.um-card{background:white;border-radius:16px;padding:24px;margin-bottom:22px;box-shadow:0 6px 22px #063b7315;border:1px solid #d9e7eb;border-top:4px solid #0796b5}.um-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:14px}.um-card label{display:block;font-weight:600;margin-bottom:6px}.um-card input,.um-card select{width:100%;box-sizing:border-box;padding:10px;border:1px solid #cbd5e1;border-radius:7px}.um-btn{padding:10px 14px;border:0;border-radius:7px;background:linear-gradient(135deg,#063b73,#0796b5);color:white;cursor:pointer;font-weight:700}.um-btn.red{background:#b91c1c}.um-btn.gray{background:#64748b}.um-table{width:100%;border-collapse:collapse}.um-table th,.um-table td{text-align:left;padding:12px;border-bottom:1px solid #e2e8f0;vertical-align:top}.um-table th{background:#063b73;color:#fff}.um-table form{margin:5px 0}.um-note{color:#64748b;font-size:14px}.um-ok{padding:12px;background:#dcfce7;color:#166534;border-radius:8px}.um-error{padding:12px;background:#fee2e2;color:#991b1b;border-radius:8px}.um-top{display:flex;justify-content:space-between;gap:12px;align-items:center;flex-wrap:wrap;background:linear-gradient(135deg,#063b73,#0796b5);color:white;padding:20px 24px;border-radius:16px;margin-bottom:22px;box-shadow:0 6px 22px #063b7325}.um-top h1{margin:0}.um-top p{color:#e0f7fa;margin-top:5px}.um-brand{display:flex;align-items:center;gap:16px}.um-brand img{width:70px;height:70px;object-fit:contain;border-radius:50%;padding:4px;background:#fff}.um-footer{display:flex;justify-content:flex-end;padding:0 0 20px}.um-modal[hidden]{display:none}.um-modal{position:fixed;inset:0;z-index:1000;display:grid;place-items:center;padding:18px;background:#0f172a99}.um-modal-card{position:relative;width:min(100%,440px);box-sizing:border-box;background:#fff;border-radius:16px;padding:26px;box-shadow:0 20px 60px #0004}.um-modal-card h2{margin:0 32px 8px 0;color:#063b73}.um-modal-card label{display:block;font-weight:600;margin:16px 0 6px}.um-modal-card input{width:100%;box-sizing:border-box;padding:10px;border:1px solid #cbd5e1;border-radius:7px}.um-modal-close{position:absolute;top:14px;right:14px;border:0;background:transparent;font-size:24px;cursor:pointer;color:#64748b}.um-modal-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:22px}@media(max-width:700px){.um-table{font-size:13px}.um-table th,.um-table td{padding:7px}.um-modal-actions>*{flex:1}}
</style></head><body><div class="um-wrap"><div class="um-top"><div class="um-brand"><img src="img/logo.png" alt="Logo del sistema"><div><h1>Administración Master</h1><p>Sesión de <?= htmlspecialchars($_SESSION['usuario_nombre'],ENT_QUOTES,'UTF-8') ?> · control de cuentas</p></div></div><div><a class="boton azul" href="admin.php">Volver al sistema</a></div></div>
<?php if($mensaje): ?><p class="um-ok"><?= htmlspecialchars($mensaje) ?></p><?php endif; ?><?php if($error): ?><p class="um-error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<section class="um-card"><h2>Crear usuario</h2><p class="um-note">Las contraseñas se guardan cifradas mediante hash; nunca se muestran en pantalla.</p><form method="POST"><input type="hidden" name="csrf_token" value="<?= $csrf ?>"><input type="hidden" name="accion" value="crear"><div class="um-grid"><div><label>Nombre</label><input name="nombre" required maxlength="100"></div><div><label>Usuario (3–40 caracteres)</label><input name="usuario" required minlength="3" maxlength="40" pattern="[A-Za-z0-9_.-]+"></div><div><label>Contraseña (mínimo 10)</label><input type="password" name="clave" required minlength="10" autocomplete="new-password"></div></div><p><button class="um-btn" type="submit">Crear usuario</button></p></form></section>
<section class="um-card"><h2>Usuarios registrados</h2><div style="overflow-x:auto"><table class="um-table"><thead><tr><th>Nombre / usuario</th><th>Rol</th><th>Contraseña</th><th>Eliminar</th></tr></thead><tbody><?php while($u=$usuarios->fetch_assoc()): ?><tr><td><strong><?= htmlspecialchars($u['nombre'],ENT_QUOTES,'UTF-8') ?></strong><br><?= htmlspecialchars($u['usuario'],ENT_QUOTES,'UTF-8') ?><br><span class="um-note">Creado: <?= htmlspecialchars($u['creado_en']) ?></span></td><td><?= $u['rol']==='master'?'Master':'Usuario' ?></td><td><button class="um-btn js-open-password-modal" type="button" data-user-id="<?= (int)$u['id'] ?>" data-user-name="<?= htmlspecialchars($u['nombre'],ENT_QUOTES,'UTF-8') ?>">Actualizar contraseña</button></td><td><?php if($u['rol']!=='master' && (int)$u['id']!==(int)$_SESSION['usuario_id']): ?><form method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este usuario?')"><input type="hidden" name="csrf_token" value="<?= $csrf ?>"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><button class="um-btn red" type="submit">Eliminar</button></form><?php else: ?><span class="um-note">Protegido</span><?php endif; ?></td></tr><?php endwhile; ?></tbody></table></div></section>
<footer class="um-footer"><a class="boton gris" href="logout.php">Cerrar sesión</a></footer></div>
<div class="um-modal" id="password-modal" role="dialog" aria-modal="true" aria-labelledby="password-modal-title" aria-describedby="password-modal-description" hidden>
<section class="um-modal-card"><button class="um-modal-close" type="button" id="close-password-modal" aria-label="Cerrar">×</button><h2 id="password-modal-title">Actualizar contraseña</h2><p id="password-modal-description" class="um-note">Ingresa la nueva contraseña para <strong id="password-modal-user"></strong>.</p><form method="POST" id="password-form"><input type="hidden" name="csrf_token" value="<?= $csrf ?>"><input type="hidden" name="accion" value="cambiar_clave"><input type="hidden" name="id" id="password-user-id">
<div id="current-password-field" hidden><label for="password-current">Contraseña actual</label><input id="password-current" type="password" name="clave_actual" autocomplete="current-password"></div>
<label for="password-new">Nueva contraseña (mínimo 10 caracteres)</label><input id="password-new" type="password" name="clave" minlength="10" required autocomplete="new-password"><label for="password-confirm">Confirmar nueva contraseña</label><input id="password-confirm" type="password" name="clave_confirmacion" minlength="10" required autocomplete="new-password"><div class="um-modal-actions"><button class="um-btn gray" type="button" id="cancel-password-modal">Cancelar</button><button class="um-btn" type="submit">Actualizar contraseña</button></div></form></section></div>
<script>
const passwordModal = document.getElementById('password-modal');
const passwordForm = document.getElementById('password-form');
const passwordUserId = document.getElementById('password-user-id');
const passwordUser = document.getElementById('password-modal-user');
const currentPasswordField = document.getElementById('current-password-field');
const passwordCurrent = document.getElementById('password-current');
const currentUserId = <?= (int)$_SESSION['usuario_id'] ?>;
const closePasswordModal = () => {
    passwordModal.hidden = true;
    passwordForm.reset();
};
document.querySelectorAll('.js-open-password-modal').forEach((button) => {
    button.addEventListener('click', () => {
        passwordUserId.value = button.dataset.userId;
        passwordUser.textContent = button.dataset.userName;
        const updatingOwnPassword = Number(button.dataset.userId) === currentUserId;
        currentPasswordField.hidden = !updatingOwnPassword;
        passwordCurrent.required = updatingOwnPassword;
        passwordModal.hidden = false;
        const firstInput = passwordForm.querySelector('input[type="password"]');
        if (firstInput) firstInput.focus();
    });
});
document.getElementById('close-password-modal').addEventListener('click', closePasswordModal);
document.getElementById('cancel-password-modal').addEventListener('click', closePasswordModal);
passwordModal.addEventListener('click', (event) => {
    if (event.target === passwordModal) closePasswordModal();
});
document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && !passwordModal.hidden) closePasswordModal();
});
passwordForm.addEventListener('submit', (event) => {
    const password = document.getElementById('password-new').value;
    const confirmation = document.getElementById('password-confirm').value;
    if (password !== confirmation) {
        event.preventDefault();
        document.getElementById('password-confirm').setCustomValidity('La confirmación de contraseña no coincide.');
        document.getElementById('password-confirm').reportValidity();
        document.getElementById('password-confirm').addEventListener('input', () => {
            document.getElementById('password-confirm').setCustomValidity('');
        }, { once: true });
    }
});
</script></body></html>