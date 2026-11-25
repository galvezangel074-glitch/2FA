<?php
// Validación de duplicados en tiempo real (la consume js/registro.js).
// Usa el usuario MySQL de solo lectura (SELECT sobre id, Usuario y NombreUsuario).
// ?tipo=correo (por defecto) o ?tipo=usuario
require_once __DIR__ . '/../comunes/init.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$tipo  = ($_GET['tipo'] ?? 'correo') === 'usuario' ? 'usuario' : 'correo';
$valor = $tipo === 'usuario'
    ? SanitizarEntrada::limpiarUsuario($_GET['usuario'] ?? '')
    : SanitizarEntrada::limpiarEmail($_GET['usuario'] ?? '');

$valido = $tipo === 'usuario'
    ? strlen($valor) >= 3
    : ($valor !== '' && filter_var($valor, FILTER_VALIDATE_EMAIL));
if (!$valido) {
    http_response_code(400);
    echo json_encode(['error' => 'Dato no válido']);
    exit;
}

try {
    $repo = new RepositorioUsuarios(new mod_db('lectura'));
    $existe = $tipo === 'usuario' ? $repo->existeNombreUsuario($valor) : $repo->existeUsuario($valor);
    echo json_encode(['existe' => $existe]);
} catch (Throwable $ex) {
    error_log('verificar_usuario: ' . $ex->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo verificar']);
}
