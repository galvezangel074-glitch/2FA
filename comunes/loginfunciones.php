<?php
// Funciones de ayuda (basadas en comunes/loginfunciones.php del repositorio)
function redireccionar(string $url)
{
    header('Location: ' . $url);
    exit;
}

function e(?string $texto): string
{
    return SanitizarEntrada::escaparHtml($texto);
}

function ipCliente(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

// Cierra la sesión por completo (datos + cookie)
function destruirSesion(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}
