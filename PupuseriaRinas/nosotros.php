<!doctype html>
<html lang="es">
  <head>
    <?php require_once __DIR__ . '/app/inicio.php'; rinas_head_publico(); ?>
    <!-- Una sola hoja para todo el frontend, con tokens, panel y las piezas
         px- de estas páginas: assets/css/estilos.css. La carga rinas_head_publico(). -->
    <!-- Marca de que hay JS: sin ella el CSS de las animaciones deja el
         contenido visible y sin movimiento (ver .js [data-reveal]). -->
    <script>document.documentElement.classList.add('js');</script>
    <title>Nosotros | Pupusería Rinas</title>
    <meta name="description" content="Conoce la historia de Pupusería Rinas: desde 2018 haciendo café y pupusas con sazón de casa. Nuestra misión, nuestra visión y el ambiente que nos representa.">
  </head>
  <body class="pagina-landing page-nosotros">
    <?php include "./vistas/plantillas/cabecera.php" ?>
    <?php include "./vistas/publicas/nosotros.php"?>
    <?php include "./vistas/plantillas/pie.php"?>
  </body>
</html>