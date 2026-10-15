<!doctype html>
<html lang="es">
  <head>
    <?php require_once __DIR__ . '/app/inicio.php'; rinas_head_publico(); ?>
    <!-- Una sola hoja para todo el frontend, con tokens, panel y las piezas
         px- de estas páginas: assets/css/estilos.css. La carga rinas_head_publico(). -->
    <!-- Marca de que hay JS: sin ella el CSS de las animaciones (reveal, línea
         a línea) y el parallax se quedan sin aplicar. -->
    <script>document.documentElement.classList.add('js');</script>
    <title>Menú | Pupusería Rinas</title>
    <meta name="description" content="Menú de Pupusería Rinas: pupusas de camarón, pollo y chile, café de la casa y licuados naturales. Consulta la carta completa y pide para llevar.">
  </head>
  <body class="pagina-landing pagina-menu">
    <?php include "./vistas/plantillas/cabecera.php" ?>
    <?php include "./vistas/publicas/menu.php"?>
    <?php include "./vistas/plantillas/pie.php"?>
  </body>
</html>