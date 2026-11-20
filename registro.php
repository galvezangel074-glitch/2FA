<?php
require_once __DIR__ . '/comunes/init.php';
require_once __DIR__ . '/comunes/plantilla.php';

$config   = require __DIR__ . '/config/config.php';
$form     = new FormularioRegistro($_POST);
$errores  = [];
$errorGeneral = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!CSRFProtection::validarToken($_POST['csrf_token'] ?? null)) {
        $errorGeneral = 'La sesión del formulario expiró. Intenta de nuevo.';
        CSRFProtection::regenerarToken();
    } elseif ($form->validar()) {
        $db   = new mod_db('web');
        $repo = new RepositorioUsuarios($db);
        if ($repo->existeUsuario($form->correo())) {
            $form->agregarError('correo', 'Este correo ya está registrado.');
        } elseif ($repo->existeNombreUsuario($form->nombreUsuario())) {
            $form->agregarError('nombre_usuario', 'Este nombre de usuario ya está en uso.');
        } else {
            $hash = new HashBcrypt();
            $dosFA = new Autenticador2FA($config['app_nombre']);
            $secret = $dosFA->generarSecret();
            try {
                // Se vincula el correo al método 2FA en el momento de crear el registro
                $repo->crear($form->nombre(), $form->apellido(), $form->correo(), $form->nombreUsuario(),
                             $hash->generar($form->clave()), $form->sexo(), $secret);
                session_regenerate_id(true);
                $_SESSION['registro_qr'] = ['correo' => $form->correo(), 'secret' => $secret];
                CSRFProtection::regenerarToken();
                redireccionar('qr_registro.php');
            } catch (PDOException $ex) {
                // Dos registros simultáneos con el mismo correo: lo frena el índice UNIQUE
                error_log('Registro: ' . $ex->getMessage());
                $form->agregarError('correo', 'Este correo o nombre de usuario ya está registrado.');
            }
        }
    }
    $errores = $form->errores();
}

function claseCampo(array $errores, string $campo): string
{
    return isset($errores[$campo]) ? ' invalido' : '';
}

encabezado('Crear cuenta');
?>
    <section class="tarjeta">
        <h1>Crear cuenta</h1>
        <p class="sub">Tu correo quedará vinculado a Google Authenticator.</p>

        <?php if ($errorGeneral !== ''): ?>
            <div class="aviso error"><?= e($errorGeneral) ?></div>
        <?php endif; ?>

        <form id="form-registro" method="post" action="registro.php" novalidate autocomplete="off">
            <?= CSRFProtection::campoHidden() ?>

            <div class="dos-columnas">
                <div class="campo<?= claseCampo($errores, 'nombre') ?>" id="campo-nombre">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" maxlength="60" required value="<?= e($form->nombre()) ?>">
                    <div class="mensaje-campo" id="msg-nombre"><?= e($errores['nombre'] ?? '') ?></div>
                </div>
                <div class="campo<?= claseCampo($errores, 'apellido') ?>" id="campo-apellido">
                    <label for="apellido">Apellido</label>
                    <input type="text" id="apellido" name="apellido" maxlength="60" required value="<?= e($form->apellido()) ?>">
                    <div class="mensaje-campo" id="msg-apellido"><?= e($errores['apellido'] ?? '') ?></div>
                </div>
            </div>

            <div class="campo<?= claseCampo($errores, 'correo') ?>" id="campo-correo">
                <label for="correo">Correo electrónico (será tu usuario)</label>
                <input type="email" id="correo" name="correo" maxlength="254" required value="<?= e($form->correo()) ?>">
                <div class="mensaje-campo" id="msg-correo"><?= e($errores['correo'] ?? '') ?></div>
            </div>

            <div class="campo<?= claseCampo($errores, 'nombre_usuario') ?>" id="campo-nombre_usuario">
                <label for="nombre_usuario">Nombre de usuario (también sirve para entrar)</label>
                <input type="text" id="nombre_usuario" name="nombre_usuario" maxlength="30" required value="<?= e($form->nombreUsuario()) ?>">
                <div class="mensaje-campo" id="msg-nombre_usuario"><?= e($errores['nombre_usuario'] ?? '') ?></div>
            </div>

            <div class="campo<?= claseCampo($errores, 'clave') ?>" id="campo-clave">
                <label for="clave">Contraseña</label>
                <div class="fila-clave">
                    <input type="password" id="clave" name="clave" maxlength="72" required autocomplete="new-password">
                    <button type="button" class="ver-clave" data-objetivo="clave">Ver</button>
                </div>
                <div class="medidor" id="medidor"><span></span></div>
                <div class="mensaje-campo" id="msg-clave"><?= e($errores['clave'] ?? '') ?></div>
            </div>

            <div class="campo<?= claseCampo($errores, 'confirmacion') ?>" id="campo-confirmacion">
                <label for="confirmacion">Confirmar contraseña</label>
                <div class="fila-clave">
                    <input type="password" id="confirmacion" name="confirmacion" maxlength="72" required autocomplete="new-password">
                    <button type="button" class="ver-clave" data-objetivo="confirmacion">Ver</button>
                </div>
                <div class="mensaje-campo" id="msg-confirmacion"><?= e($errores['confirmacion'] ?? '') ?></div>
            </div>

            <div class="campo" id="campo-sexo">
                <label>Sexo</label>
                <div class="sexo-opciones">
                    <label><input type="radio" name="sexo" value="M" <?= $form->sexo() === 'M' ? 'checked' : '' ?>> M</label>
                    <label><input type="radio" name="sexo" value="F" <?= $form->sexo() === 'F' ? 'checked' : '' ?>> F</label>
                </div>
                <div class="mensaje-campo" id="msg-sexo"><?= e($errores['sexo'] ?? '') ?></div>
            </div>

            <button type="submit" class="boton" id="btn-registrar" disabled>Registrarme</button>
        </form>

        <p class="enlaces">¿Ya tienes cuenta? <a href="login.php">Iniciar sesión</a></p>
    </section>
<?php pie('<script src="js/registro.js"></script>'); ?>
