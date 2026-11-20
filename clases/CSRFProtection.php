<?php
// Criterio: tokens Anti-CSRF (patrón de token de sincronización, como se ve en clase).
// El servidor crea un token aleatorio, lo guarda en la sesión y lo incluye en un campo oculto del formulario.
// Al recibir el POST se comparan ambos tokens; si no coinciden, se rechaza la petición.
// Un atacante no puede leer el token de la página (política del mismo origen) ni conoce el de la sesión.
class CSRFProtection
{
    // Genera el token CSRF (si no existe) y lo devuelve
    public static function generarToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    // Obtiene el token actual (cadena vacía si no hay)
    public static function obtenerToken(): string
    {
        return $_SESSION['csrf_token'] ?? '';
    }

    // Valida el token recibido con comparación segura (evita timing attacks)
    public static function validarToken(?string $tokenRecibido): bool
    {
        if (empty($_SESSION['csrf_token']) || !is_string($tokenRecibido)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $tokenRecibido);
    }

    // Campo hidden para pegar dentro del <form>
    public static function campoHidden(): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(self::generarToken(), ENT_QUOTES, 'UTF-8') . '">';
    }

    // Verifica el formulario POST; si el token no es válido responde 403 y termina
    public static function verificarFormulario(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && !self::validarToken($_POST['csrf_token'] ?? null)) {
            http_response_code(403);
            die('Error de seguridad: Token CSRF inválido');
        }
    }

    // Verifica el token en peticiones AJAX (encabezado X-CSRF-Token o campo POST)
    public static function verificarAjax(): void
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
        if (!self::validarToken((string) $token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Token CSRF inválido']);
            exit;
        }
    }

    // Regenera el token (después de acciones críticas o de un intento fallido)
    public static function regenerarToken(): string
    {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        return $_SESSION['csrf_token'];
    }

    // Meta tag para que JavaScript lo lea y lo envíe en el encabezado
    public static function metaTag(): string
    {
        return '<meta name="csrf-token" content="'
            . htmlspecialchars(self::generarToken(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

// Funciones de conveniencia (estilo procedural)
function csrf_token(): string { return CSRFProtection::obtenerToken(); }
function csrf_field(): string { return CSRFProtection::campoHidden(); }
function verificar_csrf(): void { CSRFProtection::verificarFormulario(); }
function csrf_meta(): string { return CSRFProtection::metaTag(); }
