<?php
require_once __DIR__ . '/SanitizarEntrada.php';

// Criterio: clase del Formulario de Registro. Cada método tiene UNA sola responsabilidad.
// No toca la base de datos ni el hash: eso lo hacen RepositorioUsuarios e IGeneradorHash.
final class FormularioRegistro
{
    private const LARGO_MIN_CLAVE = 8;

    private string $nombre;
    private string $apellido;
    private string $correo;
    private string $nombreUsuario;
    private string $clave;
    private string $confirmacion;
    private string $sexo;
    private array $errores = [];

    public function __construct(array $post)
    {
        $this->nombre       = SanitizarEntrada::limpiarNombre($post['nombre'] ?? '');
        $this->apellido     = SanitizarEntrada::limpiarNombre($post['apellido'] ?? '');
        $this->correo       = SanitizarEntrada::limpiarEmail($post['correo'] ?? '');
        $this->nombreUsuario = SanitizarEntrada::limpiarUsuario($post['nombre_usuario'] ?? '');
        $this->sexo        = SanitizarEntrada::limpiarSexo($post['sexo'] ?? '');
        // Las contraseñas no se "limpian": cualquier carácter es válido y modificarlas cambiaría lo que el usuario escribió
        $this->clave        = (string) ($post['clave'] ?? '');
        $this->confirmacion = (string) ($post['confirmacion'] ?? '');
    }

    public function validar(): bool
    {
        $this->errores = [];
        $this->validarRequeridos();
        $this->validarCorreo();
        $this->validarNombreUsuario();
        $this->validarClave();
        $this->validarConfirmacion();
        $this->validarSexo();
        return $this->errores === [];
    }

    private function validarRequeridos(): void
    {
        $campos = ['nombre' => $this->nombre, 'apellido' => $this->apellido, 'correo' => $this->correo];
        foreach ($campos as $campo => $valor) {
            if ($valor === '') {
                $this->errores[$campo] = 'Este campo es obligatorio.';
            }
        }
        if ($this->clave === '') {
            $this->errores['clave'] = 'Este campo es obligatorio.';
        }
    }

    private function validarCorreo(): void
    {
        if ($this->correo !== '' && !filter_var($this->correo, FILTER_VALIDATE_EMAIL)) {
            $this->errores['correo'] = 'Correo electrónico no válido.';
        }
    }

    private function validarNombreUsuario(): void
    {
        if ($this->nombreUsuario === '') {
            $this->errores['nombre_usuario'] = 'Este campo es obligatorio.';
        } elseif (strlen($this->nombreUsuario) < 3) {
            $this->errores['nombre_usuario'] = 'Mínimo 3 caracteres (letras, números, punto, guion).';
        }
    }

    private function validarClave(): void
    {
        if ($this->clave !== '' && mb_strlen($this->clave) < self::LARGO_MIN_CLAVE) {
            $this->errores['clave'] = 'La contraseña debe tener al menos ' . self::LARGO_MIN_CLAVE . ' caracteres.';
        }
    }

    private function validarConfirmacion(): void
    {
        if (!hash_equals($this->clave, $this->confirmacion)) {
            $this->errores['confirmacion'] = 'Las contraseñas no coinciden.';
        }
    }

    private function validarSexo(): void
    {
        if ($this->sexo === '') {
            $this->errores['sexo'] = 'Selecciona M o F.';
        }
    }

    public function agregarError(string $campo, string $mensaje): void
    {
        $this->errores[$campo] = $mensaje;
    }

    public function errores(): array { return $this->errores; }
    public function nombre(): string { return $this->nombre; }
    public function apellido(): string { return $this->apellido; }
    public function correo(): string { return $this->correo; }
    public function nombreUsuario(): string { return $this->nombreUsuario; }
    public function clave(): string { return $this->clave; }
    public function sexo(): string { return $this->sexo; }
}
