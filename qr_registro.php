<?php
// Muestra el código QR que se genera en el momento de crear el registro (Paso 4 de la guía).
require_once __DIR__ . '/comunes/init.php';
require_once __DIR__ . '/comunes/plantilla.php';

if (empty($_SESSION['registro_qr'])) {
    redireccionar('registro.php');
}

$config = require __DIR__ . '/config/config.php';
$correo = $_SESSION['registro_qr']['correo'];
$secret = $_SESSION['registro_qr']['secret'];
$dosFA  = new Autenticador2FA($config['app_nombre']);
$qrUrl  = $dosFA->urlQr($correo, $secret);

encabezado('Activa tu 2FA');
?>
    <section class="tarjeta">
        <div class="pasos-barra">
            <span class="hecho">1. Registro</span>
            <span class="actual">2. Escanear QR</span>
            <span>3. Iniciar sesión</span>
        </div>
        <h1>Escanea el código QR</h1>
        <p class="sub">Cuenta creada para <strong><?= e($correo) ?></strong>.</p>

        <ol class="pasos">
            <li>Abre <strong>Google Authenticator</strong> en tu teléfono.</li>
            <li>Toca <strong>+</strong> y elige <strong>Escanear un código QR</strong>.</li>
            <li>Escanea esta imagen. Verás un código de 6 dígitos que cambia cada 30 segundos.</li>
        </ol>

        <div class="qr-caja">
            <img src="<?= e($qrUrl) ?>" width="200" height="200" alt="Escanea este QR con Google Authenticator">
        </div>

        <p class="sub">Si no puedes escanear, escribe esta clave manualmente (tipo: basada en tiempo):</p>
        <div class="secreto"><?= e($secret) ?></div>

        <div class="aviso info">Guarda tu clave: esta pantalla solo se muestra al crear la cuenta.</div>
        <a class="boton verde" href="login.php?registro=ok">Ya lo escaneé, iniciar sesión</a>
    </section>
<?php pie(); ?>
