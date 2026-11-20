<?php
// FASE 2: pide el código de Google Authenticator antes de entrar al sistema (Paso 5 de la guía).
require_once __DIR__ . '/comunes/init.php';
require_once __DIR__ . '/comunes/plantilla.php';

$config = require __DIR__ . '/config/config.php';
$pre = $_SESSION['pre_2fa'] ?? null;

// Solo se llega aquí con la clave correcta y dentro del tiempo permitido
if (!$pre || (time() - (int) $pre['creada']) > $config['sesion_2fa_segundos']) {
    destruirSesion();
    redireccionar('login.php?expirada=1');
}

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRFProtection::validarToken($_POST['csrf_token'] ?? null)) {
        $mensaje = 'La sesión del formulario expiró. Intenta de nuevo.';
        CSRFProtection::regenerarToken();
    } else {
        $codigo = SanitizarEntrada::limpiarCodigo($_POST['codigo_2fa'] ?? '');
        $repo   = new RepositorioUsuarios(new mod_db('web'));
        $secret = $repo->obtenerSecret((int) $pre['id']);
        $dosFA  = new Autenticador2FA($config['app_nombre']);

        if ($secret !== null && $dosFA->verificar($secret, $codigo)) {
            session_regenerate_id(true);               // OTRA sesión: confirma que la fase del código fue exitosa
            $_SESSION = [
                'autenticado'    => 'SI',
                'fase_2fa'       => 'OK',
                'Usuario'        => $pre['usuario'],
                'usuario_id'     => $pre['id'],
                'sid_fase_clave' => $pre['sid_hash'],
                'sid_fase_codigo' => substr(hash('sha256', session_id()), 0, 12),
                'ingreso'        => time(),
            ];
            redireccionar('panel.php');
        }

        $_SESSION['pre_2fa']['intentos']++;
        if ($_SESSION['pre_2fa']['intentos'] >= $config['intentos_codigo_max']) {
            destruirSesion();
            redireccionar('login.php?expirada=1');
        }
        $mensaje = 'Código incorrecto. Acceso denegado.';
        CSRFProtection::regenerarToken();
    }
}

$restantes = $config['intentos_codigo_max'] - (int) $_SESSION['pre_2fa']['intentos'];

encabezado('Verificación en dos pasos');
?>
    <section class="tarjeta">
        <div class="pasos-barra">
            <span class="hecho">1. Usuario y contraseña</span>
            <span class="actual">2. Código 2FA</span>
        </div>
        <h1>Verificación en dos pasos</h1>
        <p class="sub">Abre Google Authenticator e ingresa el código de 6 dígitos de <strong><?= e($pre['usuario']) ?></strong>.</p>

        <?php if ($mensaje !== ''): ?>
            <div class="aviso error"><?= e($mensaje) ?> Intentos restantes: <?= (int) $restantes ?>.</div>
        <?php endif; ?>

        <form method="post" action="verificar_2fa.php" autocomplete="off">
            <?= CSRFProtection::campoHidden() ?>
            <div class="campo codigo-2fa">
                <label for="codigo_2fa">Código de verificación</label>
                <input type="text" id="codigo_2fa" name="codigo_2fa" inputmode="numeric" pattern="[0-9]{6}"
                       maxlength="6" required autofocus placeholder="000000">
            </div>
            <button type="submit" class="boton verde">Verificar y entrar</button>
        </form>

        <p class="enlaces"><a href="salir.php">Cancelar</a></p>
    </section>
<?php pie(); ?>
