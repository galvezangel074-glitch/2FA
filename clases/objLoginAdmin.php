<?php
// Basada en clases/objLoginAdmin.php (ValidacionLogin) del repositorio EjemploLogin.
// Cambios: usa la interfaz IGeneradorHash y ya no imprime mensajes con echo.
final class ValidacionLogin
{
    private ?int $id = null;
    private string $usuario;
    private string $contrasena;
    private string $hashGuardado = '';
    private string $correo = '';
    private int $loginExitoso = 0;

    public function __construct(
        string $usuario,
        string $contrasena,
        private string $ip,
        private mod_db $db,
        private IGeneradorHash $hash
    ) {
        $this->usuario    = SanitizarEntrada::limpiarIdentificador($usuario);   // correo o nombre de usuario
        $this->contrasena = $contrasena;   // la contraseña no se modifica
    }

    // Busca al usuario por correo
    public function logger(): bool
    {
        $fila = $this->db->log($this->usuario);
        if (!$fila) {
            return false;
        }
        $this->id           = (int) $fila->id;
        $this->hashGuardado = (string) $fila->HashMagic;
        $this->correo       = (string) $fila->Usuario;
        return true;
    }

    // Compara la contraseña con el hash guardado
    public function autenticar(): void
    {
        $this->loginExitoso = $this->hash->validar($this->contrasena, $this->hashGuardado) ? 1 : 0;
    }

    public function getIntentoLogin(): int { return $this->loginExitoso; }
    // Devuelve el correo guardado (la sesión siempre usa el correo, entre con correo o usuario)
    public function getUsuario(): string { return $this->correo !== '' ? $this->correo : $this->usuario; }
    public function getId(): ?int { return $this->id; }

    // Guarda el intento (correcto o no) con la IP, como en el repositorio
    public function registrarIntentos(): void
    {
        $this->db->insertSeguro('intentos_login', [
            'Usuario'            => $this->usuario,
            'ipRemoto'           => $this->ip,
            'deteccion_anomalia' => $this->loginExitoso,
        ]);
    }
}
