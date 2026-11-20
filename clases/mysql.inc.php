<?php
// Conexión PDO con usuarios de privilegios mínimos (basada en clases/mysql.inc.php del repositorio EjemploLogin)
class mod_db
{
    private PDO $conexion;

    public function __construct(string $perfil = 'web')
    {
        $cfg = require __DIR__ . '/../config/config.php';
        if (!isset($cfg['perfiles'][$perfil])) {
            throw new InvalidArgumentException('Perfil de base de datos no válido');
        }
        $p = $cfg['perfiles'][$perfil];
        try {
            $this->conexion = new PDO($cfg['dsn'], $p['usuario'], $p['clave'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('Error de conexión: ' . $e->getMessage());
            http_response_code(500);
            exit('No se pudo conectar con la base de datos.');
        }
    }

    public function getConexion(): PDO
    {
        return $this->conexion;
    }

    // Busca un usuario por correo (sentencia preparada)
    public function log(string $usuario): object|false
    {
        // Acepta correo (columna Usuario) o nombre de usuario (columna NombreUsuario)
        $stmt = $this->conexion->prepare('SELECT * FROM usuarios WHERE Usuario = :c OR NombreUsuario = :n LIMIT 1');
        $stmt->bindValue(':c', $usuario, PDO::PARAM_STR);
        $stmt->bindValue(':n', $usuario, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch();
    }

    // INSERT con sentencia preparada. El nombre de tabla y columnas vienen del código, nunca del usuario.
    public function insertSeguro(string $tabla, array $data): bool
    {
        foreach (array_merge([$tabla], array_keys($data)) as $identificador) {
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', (string) $identificador)) {
                throw new InvalidArgumentException('Identificador SQL no válido');
            }
        }
        $columnas = implode(', ', array_keys($data));
        $marcas   = ':' . implode(', :', array_keys($data));
        $stmt = $this->conexion->prepare("INSERT INTO $tabla ($columnas) VALUES ($marcas)");
        foreach ($data as $clave => $valor) {
            $stmt->bindValue(':' . $clave, $valor);
        }
        return $stmt->execute();
    }

    public function disconnect(): void
    {
        unset($this->conexion);
    }
}
