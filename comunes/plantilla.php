<?php
// Cabecera y pie comunes para todas las páginas.
function encabezado(string $titulo, string $clase = ''): void
{
    ?><!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($titulo) ?> | Parcial 2FA</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body class="<?= e($clase) ?>">
<main class="contenedor">
    <header class="marca">
        <span class="marca-icono" aria-hidden="true">&#128274;</span>
        <div>
            <strong>Parcial 2FA</strong>
            <small>Desarrollo Web | UTP</small>
        </div>
    </header>
<?php
}

function pie(string $scripts = ''): void
{
    ?>
    <footer class="pie">
        &copy; Universidad Tecnol&oacute;gica de Panam&aacute; | Desarrollo Web <?= date('Y') ?>
    </footer>
</main>
<?= $scripts ?>
</body>
</html>
<?php
}
