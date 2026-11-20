-- Migración para bases de datos ya creadas: agrega el nombre de usuario (login con correo o usuario)
-- Ejecutar:  mysql -u root -p < 03_agregar_nombre_usuario.sql
USE parcial2fa;

ALTER TABLE `usuarios`
    ADD `NombreUsuario` VARCHAR(30) NULL AFTER `Usuario`,
    ADD UNIQUE KEY `uq_usuarios_nombreusuario` (`NombreUsuario`);
