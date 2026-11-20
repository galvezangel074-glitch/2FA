<?php
// Validación de duplicados en tiempo real (la consume js/registro.js).
// Usa el usuario MySQL de solo lectura (SELECT sobre id y Usuario).
require_once __DIR__ . '/../comunes/init.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$correo = SanitizarEntrada::limpiarEmail($_GET['usuario'] ?? '');
if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['error' => 'Correo no válido']);
    exit;
}

try {
    $repo = new RepositorioUsuarios(new mod_db('lectura'));
    echo json_encode(['existe' => $repo->existeUsuario($correo)]);
} catch (Throwable $ex) {
    error_log('verificar_usuario: ' . $ex->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo verificar']);
}
