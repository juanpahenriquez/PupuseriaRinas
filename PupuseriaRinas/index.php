<!doctype html>
<html lang="es">
  <head>
    <?php require_once "./php/enlaces.php"; rinas_head_publico(); ?>
    <link href="assets/css/landing.css?v=<?= LINK_CSS_VERSION ?>" rel="stylesheet">
    <!-- Marca de que hay JS: sin ella el CSS de las animaciones deja el
         contenido visible y sin movimiento (ver .js [data-reveal]). -->
    <script>document.documentElement.classList.add('js');</script>
    <title>Pupusería Rinas | Café caliente y pupusas hechas al momento</title>
    <meta name="description" content="Pupusas recién hechas, café de la casa y licuados naturales en San Salvador. Recoge en tienda o pide para llevar.">
  </head>
  <body class="pagina-landing">
    <?php include "./modules/views/layouts/header.php" ?>
    <?php include "./modules/views/principal/landing.php"?>
    <?php include "./modules/views/layouts/footer.php"?>