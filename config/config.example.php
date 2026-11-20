<?php
// Configuración de conexión. NUNCA se usa el superusuario root.
// Las contraseñas deben coincidir con sql/02_usuarios_privilegios_minimos.sql
return [
    'app_nombre' => 'ParcialDosFA',   // nombre que aparece en Google Authenticator (sin ":")
    'dsn'        => 'mysql:host=localhost;dbname=parcial2fa;charset=utf8mb4',
    'perfiles'   => [
        'web' => [
            'usuario' => 'app2fa_web',
            'clave'   => 'CAMBIAR_CLAVE_WEB',
        ],
        'lectura' => [
            'usuario' => 'app2fa_lectura',
            'clave'   => 'CAMBIAR_CLAVE_LECTURA',
        ],
    ],
    'sesion_2fa_segundos' => 300,   // tiempo máximo entre clave correcta y código
    'intentos_codigo_max' => 5,
];
