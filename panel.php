<?php
require_once __DIR__ . '/comunes/bloque_Seguridad.php';
require_once __DIR__ . '/comunes/plantilla.php';

encabezado('Panel', 'ancho');
?>
    <section class="tarjeta">
        <h1>Acceso concedido</h1>
        <p class="sub">Bienvenido, <strong><?= e($_SESSION['Usuario']) ?></strong>.</p>

        <div class="aviso ok">Ambas fases de autenticación fueron exitosas.</div>

        <table class="datos">
            <tr><th>Fase 1</th><td>Usuario y contraseña <span class="etiqueta-ok">&#10003; correcta</span></td></tr>
            <tr><th>Fase 2</th><td>Código de Google Authenticator <span class="etiqueta-ok">&#10003; correcto</span></td></tr>
            <tr><th>Sesión de la fase 1</th><td><code><?= e($_SESSION['sid_fase_clave']) ?></code> (huella del ID)</td></tr>
            <tr><th>Sesión nueva tras el código</th><td><code><?= e($_SESSION['sid_fase_codigo']) ?></code> (huella del ID; distinta a la anterior)</td></tr>
            <tr><th>Ingreso</th><td><?= e(date('d/m/Y H:i:s', (int) $_SESSION['ingreso'])) ?></td></tr>
        </table>

        <a class="boton" href="privilegios.php">Ver usuarios MySQL y sus privilegios</a>
        <p class="enlaces"><a href="salir.php">Cerrar sesión</a></p>
    </section>
<?php pie(); ?>
