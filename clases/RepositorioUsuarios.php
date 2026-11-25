<?php
// Acceso a datos de usuarios. Todas las consultas usan sentencias preparadas.
final class RepositorioUsuarios
{
    public function __construct(private mod_db $db) {}

    // Usa el perfil de solo lectura cuando se le pasa uno (validación de duplicados)
    public function existeUsuario(string $correo): bool
    {
        $stmt = $this->db->getConexion()->prepare('SELECT id FROM usuarios WHERE Usuario = :u LIMIT 1');
        $stmt->bindValue(':u', $correo, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    public function existeNombreUsuario(string $nombreUsuario): bool
    {
        $stmt = $this->db->getConexion()->prepare('SELECT id FROM usuarios WHERE NombreUsuario = :n OR Usuario = :n2 LIMIT 1');
        $stmt->bindValue(':n', $nombreUsuario, PDO::PARAM_STR);
        $stmt->bindValue(':n2', $nombreUsuario, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch() !== false;
    }

    public function crear(string $nombre, string $apellido, string $correo, string $nombreUsuario, string $hash, string $sexo, string $secret): int
    {
        $this->db->insertSeguro('usuarios', [
            'Nombre'     => $nombre,
            'Apellido'   => $apellido,
            'Usuario'    => $correo,
            'NombreUsuario' => $nombreUsuario,
            'HashMagic'  => $hash,
            'secret_2fa' => SanitizarEntrada::limpiarSecreto($secret),
            'Sexo'       => $sexo,
        ]);
        return (int) $this->db->getConexion()->lastInsertId();
    }

    public function obtenerSecret(int $id): ?string
    {
        $stmt = $this->db->getConexion()->prepare('SELECT secret_2fa FROM usuarios WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $fila = $stmt->fetch();
        if (!$fila || $fila->secret_2fa === null) {
            return null;
        }
        $secret = SanitizarEntrada::limpiarSecreto((string) $fila->secret_2fa);
        return $secret !== '' ? $secret : null;
    }
}
