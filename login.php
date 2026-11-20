<?php
// FASE 1: usuario y contraseña. Si son correctos, pasa a verificar_2fa.php (no entra al sistema todavía).
require_once __DIR__ . '/comunes/init.php';
require_once __DIR__ . '/comunes/plantilla.php';

if (($_SESSION['autenticado'] ?? '') === 'SI' && ($_SESSION['fase_2fa'] ?? '') === 'OK') {
    redireccionar('panel.php');
}

$mensaje = '';
$tipoMensaje = 'error';
$correoPrevio = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correoPrevio = SanitizarEntrada::limpiarIdentificador($_POST['usuario'] ?? '');

    if (!CSRFProtection::validarToken($_POST['csrf_token'] ?? null)) {
        $mensaje = 'La sesión del formulario expiró. Intenta de nuevo.';
        CSRFProtection::regenerarToken();
    } else {
        $db    = new mod_db('web');
        $hash  = new HashBcrypt();
        $login = new ValidacionLogin($correoPrevio, (string) ($_POST['contrasena'] ?? ''), ipCliente(), $db, $hash);

        if ($login->logger()) {
            $login->autenticar();
        } else {
            // Usuario inexistente: se gasta el mismo tiempo de cálculo para no revelar si el correo existe
            $hash->generar((string) ($_POST['contrasena'] ?? ''));
        }
        $login->registrarIntentos();

        if ($login->getIntentoLogin() === 1) {
            $sidAnterior = session_id();
            session_regenerate_id(true);               // sesión nueva al terminar la fase 1
            $_SESSION = [];
            $_SESSION['pre_2fa'] = [
                'id'       => $login->getId(),
                'usuario'  => $login->getUsuario(),
                'creada'   => time(),
                'intentos' => 0,
                'sid_hash' => substr(hash('sha256', $sidAnterior), 0, 12),
            ];
            redireccionar('verificar_2fa.php');
        }
        $mensaje = 'Usuario/correo o contraseña incorrectos.';
        CSRFProtection::regenerarToken();
    }
} else {
    unset($_SESSION['registro_qr'], $_SESSION['pre_2fa']);
    if (($_GET['registro'] ?? '') === 'ok') {
        $mensaje = 'Cuenta creada. Ya puedes iniciar sesión.';
        $tipoMensaje = 'ok';
    } elseif (($_GET['expirada'] ?? '') === '1') {
        $mensaje = 'La verificación expiró. Inicia sesión de nuevo.';
    }
}

encabezado('Iniciar sesión');
?>
    <section class="tarjeta">
        <div class="pasos-barra">
            <span class="actual">1. Usuario y contraseña</span>
            <span>2. Código 2FA</span>
        </div>
        <h1>Iniciar sesión</h1>
        <p class="sub">Ingresa tus credenciales para continuar.</p>

        <?php if ($mensaje !== ''): ?>
            <div class="aviso <?= e($tipoMensaje) ?>"><?= e($mensaje) ?></div>
        <?php endif; ?>

        <form method="post" action="login.php" id="form-login" autocomplete="off">
            <?= CSRFProtection::campoHidden() ?>
            <div class="campo">
                <label for="usuario">Correo electrónico o usuario</label>
                <input type="text" id="usuario" name="usuario" required maxlength="254" value="<?= e($correoPrevio) ?>" autofocus>
            </div>
            <div class="campo">
                <label for="contrasena">Contraseña</label>
                <div class="fila-clave">
                    <input type="password" id="contrasena" name="contrasena" required maxlength="72" autocomplete="current-password">
                    <button type="button" class="ver-clave" data-objetivo="contrasena">Ver</button>
                </div>
            </div>
            <button type="submit" class="boton">Continuar</button>
        </form>

        <p class="enlaces">¿No tienes cuenta? <a href="registro.php">Crear cuenta</a></p>
    </section>
<?php
pie('<script>
document.querySelectorAll(".ver-clave").forEach(function (b) {
    b.addEventListener("click", function () {
        var i = document.getElementById(b.getAttribute("data-objetivo"));
        var oculto = i.type === "password";
        i.type = oculto ? "text" : "password";
        b.textContent = oculto ? "Ocultar" : "Ver";
    });
});
</script>');
