-- =====================================================================
-- Criterio 1 (15 pts): usuarios con privilegios mínimos. NO se usa root.
-- Ejecutar con un usuario administrador:  mysql -u root -p < 02_usuarios_privilegios_minimos.sql
-- Si cambias las contraseñas, cámbialas también en config/config.php
-- =====================================================================

DROP USER IF EXISTS 'app2fa_web'@'localhost';
DROP USER IF EXISTS 'app2fa_lectura'@'localhost';

-- Usuario 1: lo usa la aplicación (login, registro, bitácora de intentos)
CREATE USER 'app2fa_web'@'localhost' IDENTIFIED BY 'Web#2FA_Cambiar_2026';
GRANT SELECT, INSERT ON parcial2fa.usuarios        TO 'app2fa_web'@'localhost';
GRANT INSERT         ON parcial2fa.intentos_login  TO 'app2fa_web'@'localhost';

-- Usuario 2: solo lectura de tres columnas; lo usa la validación de duplicados (AJAX)
CREATE USER 'app2fa_lectura'@'localhost' IDENTIFIED BY 'Lect#2FA_Cambiar_2026';
GRANT SELECT (id, Usuario, NombreUsuario) ON parcial2fa.usuarios TO 'app2fa_lectura'@'localhost';

-- ---------------------------------------------------------------------
-- Comando que permite ver los privilegios concedidos (mostrar en pantalla)
-- ---------------------------------------------------------------------
SHOW GRANTS FOR 'app2fa_web'@'localhost';
SHOW GRANTS FOR 'app2fa_lectura'@'localhost';

-- Ver los usuarios creados
SELECT User, Host FROM mysql.user WHERE User LIKE 'app2fa%';
