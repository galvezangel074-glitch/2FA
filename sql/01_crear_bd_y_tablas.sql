-- =====================================================================
-- PASO 1 de la guía: base de datos y tablas (2 tablas)
-- Ejecutar con un usuario administrador:  mysql -u root -p < 01_crear_bd_y_tablas.sql
-- =====================================================================
CREATE DATABASE IF NOT EXISTS parcial2fa
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE parcial2fa;

-- Tabla 1: usuarios (sin secret_2fa; se agrega con el ALTER de la guía)
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `Nombre`         VARCHAR(60)  NOT NULL,
    `Apellido`       VARCHAR(60)  NOT NULL,
    `Usuario`        VARCHAR(254) NOT NULL,            -- correo electrónico = nombre de usuario
    `NombreUsuario`  VARCHAR(30)  NULL,                -- nombre de usuario alterno (también sirve para entrar)
    `HashMagic`      VARCHAR(255) NOT NULL,            -- hash bcrypt de la contraseña
    `Sexo`           ENUM('M','F') NOT NULL,
    `fecha_registro` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_usuarios_usuario` (`Usuario`),
    UNIQUE KEY `uq_usuarios_nombreusuario` (`NombreUsuario`)
) ENGINE=InnoDB;

-- Sentencia textual de la guía (Paso 1)
ALTER TABLE `usuarios`
ADD `secret_2fa` VARCHAR(255) NULL AFTER `HashMagic`;

-- Tabla 2: intentos_login (registro de cada intento de inicio de sesión)
CREATE TABLE IF NOT EXISTS `intentos_login` (
    `id`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `Usuario`           VARCHAR(254) NOT NULL,
    `ipRemoto`          VARCHAR(45)  NOT NULL,
    `deteccion_anomalia` TINYINT(1)  NOT NULL DEFAULT 0,  -- 1 = clave correcta, 0 = clave incorrecta
    `fecha`             TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_intentos_usuario` (`Usuario`)
) ENGINE=InnoDB;
