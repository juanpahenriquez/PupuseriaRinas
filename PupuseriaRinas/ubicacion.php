<!doctype html>
<html lang="es">
  <head>
    <?php require_once __DIR__ . '/app/inicio.php'; rinas_head_publico(); ?>
    <!-- Una sola hoja para todo el frontend, con tokens, panel y las piezas
         px- de estas páginas: assets/css/estilos.css. La carga rinas_head_publico(). -->
    <!-- Marca de que hay JS: sin ella el CSS de las animaciones (reveal) y el
         parallax se quedan sin aplicar. -->
    <script>document.documentElement.classList.add('js');</script>
    <title>Ubicación | Pupusería Rinas</title>
    <meta name="description" content="Encuentra Pupusería Rinas: dirección, horarios de atención y mapa para cómo llegar. Café caliente y pupusas hechas al momento en San Salvador.">
  </head>
  <body class="pagina-landing pagina-ubicacion">
    <?php include "./vistas/plantillas/cabecera.php" ?>
    <?php include "./vistas/publicas/ubicacion.php"?>
    <?php include "./vistas/plantillas/pie.php"?>
  </body>
</html>