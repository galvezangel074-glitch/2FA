<?php
// Criterio 1: muestra en pantalla los usuarios creados, sus privilegios y el comando que permite verlos.
require_once __DIR__ . '/comunes/bloque_Seguridad.php';
require_once __DIR__ . '/comunes/plantilla.php';

$config = require __DIR__ . '/config/config.php';

$bloques = [];
foreach ($config['perfiles'] as $perfil => $datos) {
    $bloque = ['usuario' => $datos['usuario'], 'comando' => "SHOW GRANTS FOR '{$datos['usuario']}'@'localhost';",
               'grants' => [], 'error' => ''];
    try {
        // Cada usuario se conecta con su propia cuenta y consulta SUS privilegios
        $db = new mod_db($perfil);
        $filas = $db->getConexion()->query('SHOW GRANTS FOR CURRENT_USER()')->fetchAll(PDO::FETCH_NUM);
        foreach ($filas as $f) { $bloque['grants'][] = $f[0]; }
        $db->disconnect();
    } catch (Throwable $ex) {
        error_log('privilegios.php: ' . $ex->getMessage());
        $bloque['error'] = 'No se pudo consultar los privilegios de este usuario.';
    }
    $bloques[] = $bloque;
}

encabezado('Privilegios MySQL', 'ancho');
?>
    <section class="tarjeta">
        <h1>Usuarios MySQL y privilegios</h1>
        <p class="sub">La aplicación no usa <code>root</code>. Cada usuario tiene solo lo que necesita.</p>

        <?php foreach ($bloques as $b): ?>
            <h3>Usuario: <code><?= e($b['usuario']) ?>@localhost</code></h3>
            <p>Comando para ver sus privilegios:</p>
            <pre class="sql"><?= e($b['comando']) ?></pre>
            <?php if ($b['error'] !== ''): ?>
                <div class="aviso error"><?= e($b['error']) ?></div>
            <?php else: ?>
                <table class="datos">
                    <tr><th>Privilegios concedidos</th></tr>
                    <?php foreach ($b['grants'] as $g): ?>
                        <tr><td><code><?= e($g) ?></code></td></tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        <?php endforeach; ?>

        <a class="boton gris" href="panel.php">Volver al panel</a>
    </section>
<?php pie(); ?>
