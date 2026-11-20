<?php
// Criterio: clase con métodos ESTÁTICOS para sanitizar datos (mínimo 3). Aquí hay 6.
// Amplía SanitizarEntrada::limpiarCadena() del repositorio EjemploLogin.
class SanitizarEntrada
{
    // 1. Texto general: quita espacios, etiquetas HTML y caracteres de control
    public static function limpiarCadena(?string $cadena): string
    {
        $cadena = trim(strip_tags((string) $cadena));
        return preg_replace('/[\x00-\x1F\x7F]/u', '', $cadena) ?? '';
    }

    // 2. Correo: minúsculas y solo caracteres permitidos en un email
    public static function limpiarEmail(?string $email): string
    {
        $email = filter_var(trim((string) $email), FILTER_SANITIZE_EMAIL);
        return mb_strtolower($email === false ? '' : $email, 'UTF-8');
    }

    // 2b. Nombre de usuario: minúsculas, solo a-z 0-9 . _ - (sin @, para no chocar con los correos)
    public static function limpiarUsuario(?string $usuario): string
    {
        return substr(preg_replace('/[^a-z0-9._\-]/', '', mb_strtolower(trim((string) $usuario), 'UTF-8')) ?? '', 0, 30);
    }

    // 2c. Identificador de login: correo o usuario (se decide por la presencia de @)
    public static function limpiarIdentificador(?string $valor): string
    {
        $valor = trim((string) $valor);
        return str_contains($valor, '@') ? self::limpiarEmail($valor) : self::limpiarUsuario($valor);
    }

    // 3. Nombres/apellidos: solo letras (con tildes), espacios, apóstrofo y guion
    public static function limpiarNombre(?string $nombre): string
    {
        $nombre = self::limpiarCadena($nombre);
        $nombre = preg_replace('/[^\p{L}\s\'\-]/u', '', $nombre) ?? '';
        return trim(preg_replace('/\s+/u', ' ', $nombre) ?? '');
    }

    // 4. Sexo: solo "M" o "F"; cualquier otro valor queda vacío
    public static function limpiarSexo(?string $sexo): string
    {
        $sexo = strtoupper(self::limpiarCadena($sexo));
        return in_array($sexo, ['M', 'F'], true) ? $sexo : '';
    }

    // 5. Código del autenticador: solo dígitos, máximo 6
    public static function limpiarCodigo(?string $codigo): string
    {
        return substr(preg_replace('/\D+/', '', (string) $codigo) ?? '', 0, 6);
    }

    // 6. Salida a HTML (evita XSS al imprimir datos)
    public static function escaparHtml(?string $texto): string
    {
        return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
