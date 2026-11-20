<?php
// Autoload mínimo (PSR-4) para usar la librería sin ejecutar Composer.
// Si ejecutas `composer install`, Composer reemplaza este archivo por el suyo.
spl_autoload_register(function (string $clase): void {
    $prefijos = [
        'Sonata\\GoogleAuthenticator\\' => __DIR__ . '/sonata-project/google-authenticator/src/',
        'Google\\Authenticator\\'       => __DIR__ . '/sonata-project/google-authenticator/src/',
    ];
    foreach ($prefijos as $prefijo => $dir) {
        if (strncmp($clase, $prefijo, strlen($prefijo)) === 0) {
            $archivo = $dir . str_replace('\\', '/', substr($clase, strlen($prefijo))) . '.php';
            if (is_file($archivo)) { require $archivo; }
            return;
        }
    }
});
