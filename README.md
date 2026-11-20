# Parcial práctico 2FA - Desarrollo Web (UTP)

## Instalación en WAMP con VS Code (Windows)

1. Inicia WAMP y espera el icono **verde**. Requiere PHP 8.0 o superior (WAMP > PHP > Versión) y las extensiones `pdo_mysql` y `mbstring` activas (vienen activas por defecto).
2. Copia la carpeta `Parcial2FA` a `C:\wamp64\www\Parcial2FA`.
3. Abre la carpeta en VS Code: `Archivo > Abrir carpeta` y elige `C:\wamp64\www\Parcial2FA`. Acepta instalar las extensiones recomendadas (Intelephense, PHP Debug). En `.vscode/settings.json` ajusta la ruta de `php.exe` a la versión de PHP que tengas en `C:\wamp64\bin\php\`.
4. Crea la base de datos y los usuarios con **phpMyAdmin** (`http://localhost/phpmyadmin`, usuario `root`, clave vacía en WAMP por defecto):
   - Pestaña **Importar** > elige `sql/01_crear_bd_y_tablas.sql` > Continuar.
   - Pestaña **Importar** > elige `sql/02_usuarios_privilegios_minimos.sql` > Continuar.
   - Comprueba en la pestaña **SQL**: `SHOW GRANTS FOR 'app2fa_web'@'localhost';`
5. Abre `http://localhost/Parcial2FA/registro.php`.
6. La librería de Google Authenticator ya está en `vendor/`; no necesitas Composer. Si quieres usarlo: en la terminal de VS Code, `composer require sonata-project/google-authenticator`.

Si algo falla: errores de PHP en `C:\wamp64\logs\php_error.log`; de Apache en `C:\wamp64\logs\apache_error.log`.

---

Login con autenticación de dos factores (PHP + MySQL + Google Authenticator), basado en el
repositorio `EjemploLogin` (clases `mod_db`, `SanitizarEntrada`, `ValidacionLogin`, `loginfunciones`).

## Puesta en marcha

1. Requisitos: PHP 8+, MySQL 8, Apache/Nginx o `php -S`, Google Authenticator en el celular.
2. Librería (Paso 3 de la guía). La carpeta `vendor/` ya trae `sonata-project/google-authenticator`.
   Si prefieres Composer: `composer require sonata-project/google-authenticator`
3. Base de datos (como administrador de MySQL):
   ```
   mysql -u root -p < sql/01_crear_bd_y_tablas.sql
   mysql -u root -p < sql/02_usuarios_privilegios_minimos.sql
   ```
4. Si cambias las contraseñas de los usuarios MySQL, cámbialas en `config/config.php`.
5. Servidor: con WAMP, `http://localhost/Parcial2FA/registro.php` (alternativa sin WAMP: `php -S localhost:8000`).

## Flujo

Registro (`registro.php`) → QR (`qr_registro.php`) → Login (`login.php`) → Código (`verificar_2fa.php`) → `panel.php`.

## Pasos y actividades de la guía

| Guía | Dónde está |
|---|---|
| Paso 1: `ALTER TABLE usuarios ADD secret_2fa ... AFTER HashMagic` | `sql/01_crear_bd_y_tablas.sql` |
| Paso 2: formulario Nombre, Apellido, Correo (usuario), contraseña (HashMagic), Sexo M/F; `secret_2fa` no se llena a mano | `registro.php` |
| Paso 3: librería Google Authenticator | `composer.json`, `vendor/` |
| Paso 4: generar secreto y QR | `clases/Autenticador2FA.php`, `qr_registro.php` |
| Paso 5: verificar el código | `verificar_2fa.php` |
| Act. 1: sistema de login con soporte 2FA | `login.php` + `verificar_2fa.php` |
| Act. 2: generar y guardar `secret_2fa` dinámicamente | `registro.php` → `RepositorioUsuarios::crear()` |
| Act. 3: mostrar QR | `qr_registro.php` |
| Act. 4: segunda pantalla para el código | `verificar_2fa.php` |
| Act. 5: validar el código antes del acceso completo | `comunes/bloque_Seguridad.php` (exige las dos fases) |

## Rúbrica del parcial (100 pts)

| Criterio | Pts | Implementación |
|---|---|---|
| Usuarios con privilegios mínimos, sin root; mostrar privilegios y comando | 15 | `sql/02_usuarios_privilegios_minimos.sql`; pantalla `privilegios.php` (comando `SHOW GRANTS FOR ...` y resultado) |
| Formulario de registro con validaciones básicas | 5 | `registro.php`, `js/registro.js` |
| Validar correo/usuario no duplicado (frontend) | 5 | `js/registro.js` + `ajax/verificar_usuario.php`; el servidor y el índice UNIQUE también lo verifican |
| Clase del formulario, métodos de mínima responsabilidad | 10 | `clases/FormularioRegistro.php` |
| Sanitizar con métodos estáticos (mín. 3) | 10 | `clases/SanitizarEntrada.php` (6 métodos) |
| Genera el código QR | 5 | `qr_registro.php` |
| Inicia sesión y pide el código antes de entrar | 5 | `login.php` → `verificar_2fa.php` |
| Otra sesión que confirma la fase del código | 5 | `session_regenerate_id(true)` en cada fase; `panel.php` muestra las dos huellas de ID, distintas |
| Interfaz para generar y validar hash (POO) | 5 | `clases/IGeneradorHash.php`, `clases/HashBcrypt.php` |
| Tokens Anti-CSRF | 10 | `clases/CSRFProtection.php` en registro, login y verificación |
| Tablas con datos limpios y consistentes (5 pts por tabla) | 10 | `usuarios` e `intentos_login` |
| Calidad del CSS | 10 | `css/estilo.css` (azul, gris, verde), medidor de contraseña, ver/ocultar clave, pasos |

## Notas

- Se usa el correo como nombre de usuario, como indica la guía (columna `Usuario`).
- El QR se genera con `api.qrserver.com`, como en la guía: el secreto viaja a ese servicio. Para producción conviene generar el QR en el servidor.
- `secret_2fa` se guarda en texto plano, como en la guía.
- Tras 5 códigos incorrectos o 5 minutos, se vuelve al login.
