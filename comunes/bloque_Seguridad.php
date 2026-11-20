<?php
// Bloque de seguridad: solo entra quien completó AMBAS fases (clave + código 2FA).
require_once __DIR__ . '/init.php';

if (($_SESSION['autenticado'] ?? '') !== 'SI' || ($_SESSION['fase_2fa'] ?? '') !== 'OK') {
    // Con la clave correcta pero sin código, se le devuelve a la pantalla del código
    redireccionar(isset($_SESSION['pre_2fa']) ? 'verificar_2fa.php' : 'login.php');
}
