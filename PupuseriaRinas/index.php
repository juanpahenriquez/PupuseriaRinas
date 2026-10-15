<!doctype html>
<html lang="es">
  <head>
    <?php require_once __DIR__ . '/app/inicio.php'; rinas_head_publico(); ?>
    <!-- Marca de que hay JS: sin ella el CSS de las animaciones deja el
         contenido visible y sin movimiento (ver .js [data-reveal]). -->
    <script>document.documentElement.classList.add('js');</script>
    <title>Pupusería Rinas | Café caliente y pupusas hechas al momento</title>
    <meta name="description" content="Pupusas recién hechas, café de la casa y licuados naturales en San Salvador. Recoge en tienda o pide para llevar.">
  </head>
  <body class="pagina-landing">
    <?php include "./vistas/plantillas/cabecera.php" ?>
    <?php include "./vistas/publicas/portada.php"?>
    <?php include "./vistas/plantillas/pie.php"?>