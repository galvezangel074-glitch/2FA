<?php
// Criterio: tokens Anti-CSRF.
// Al mostrar el formulario se crea un token aleatorio, se guarda en la sesión del servidor y se envía
// en un campo oculto. Al recibir el formulario se comparan ambos tokens; si no coinciden, se rechaza.
// Un atacante no puede leer el token de la página (política del mismo origen) ni conoce el de la sesión.
final class TokenCsrf
{
    private const CLAVE = 'csrf_tokens';

    // Devuelve el token del formulario (lo crea si no existe)
    public static function generar(string $formulario): string
    {
        if (empty($_SESSION[self::CLAVE][$formulario])) {
            $_SESSION[self::CLAVE][$formulario] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::CLAVE][$formulario];
    }

    // Campo oculto listo para pegar dentro del <form>
    public static function campo(string $formulario): string
    {
        return '<input type="hidden" name="csrf_token" value="'
            . htmlspecialchars(self::generar($formulario), ENT_QUOTES, 'UTF-8') . '">';
    }

    // Compara en tiempo constante el token recibido contra el de la sesión
    public static function validar(string $formulario, ?string $recibido): bool
    {
        $guardado = $_SESSION[self::CLAVE][$formulario] ?? '';
        return is_string($recibido) && $guardado !== '' && hash_equals($guardado, $recibido);
    }

    // Se invalida tras un uso correcto: cada token sirve una sola vez
    public static function renovar(string $formulario): void
    {
        unset($_SESSION[self::CLAVE][$formulario]);
    }
}
