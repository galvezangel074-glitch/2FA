<?php
use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

// Envuelve sonata-project/google-authenticator (Pasos 4 y 5 de la guía)
final class Autenticador2FA
{
    private GoogleAuthenticator $g;

    public function __construct(private string $nombreApp)
    {
        $this->g = new GoogleAuthenticator();
    }

    // Paso 4: genera el secreto TOTP del usuario
    public function generarSecret(): string
    {
        return $this->g->generateSecret();
    }

    // Paso 4: URL de la imagen QR (otpauth://totp/App:correo?secret=...&issuer=App)
    // GoogleQrUrl::generate() ya devuelve la URL completa de api.qrserver.com.
    public function urlQr(string $correo, string $secret): string
    {
        return GoogleQrUrl::generate($correo, $secret, $this->nombreApp);
    }

    // Paso 5: verifica el código de 6 dígitos (tolera ±30 s de desfase de reloj)
    public function verificar(string $secret, string $codigo): bool
    {
        return strlen($codigo) === 6 && $this->g->checkCode($secret, $codigo);
    }

    // Solo para pruebas automáticas: calcula el código vigente
    public function codigoActual(string $secret): string
    {
        return $this->g->getCode($secret);
    }
}
