<?php
// Arranque común: sesión segura, autocarga de clases y funciones de ayuda.
declare(strict_types=1);
date_default_timezone_set('America/Panama');

require_once __DIR__ . '/../vendor/autoload.php';
foreach (['SanitizarEntrada', 'IGeneradorHash', 'HashBcrypt', 'CSRFProtection', 'FormularioRegistro',
          'mysql.inc', 'RepositorioUsuarios', 'Autenticador2FA', 'objLoginAdmin'] as $clase) {
    require_once __DIR__ . '/../clases/' . $clase . '.php';
}
require_once __DIR__ . '/loginfunciones.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,       // JavaScript no puede leer la cookie de sesión
        'samesite' => 'Lax',      // refuerzo contra CSRF
    ]);
    session_start();
}
