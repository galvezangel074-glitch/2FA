<?php
require_once __DIR__ . '/IGeneradorHash.php';

// Implementación con bcrypt (password_hash / password_verify), como en VerificacionPassword.php del repositorio
final class HashBcrypt implements IGeneradorHash
{
    private array $opciones;

    public function __construct(int $costo = 12)
    {
        $this->opciones = ['cost' => $costo];
    }

    public function generar(string $clave): string
    {
        return password_hash($clave, PASSWORD_BCRYPT, $this->opciones);
    }

    public function validar(string $clave, string $hash): bool
    {
        return password_verify($clave, $hash);
    }

    public function necesitaRehash(string $hash): bool
    {
        return password_needs_rehash($hash, PASSWORD_BCRYPT, $this->opciones);
    }
}
