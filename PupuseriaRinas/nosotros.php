<!doctype html>
<html lang="en">
  <head>
    <?php require_once "./php/enlaces.php"; rinas_head_publico(); ?>
    <!-- Marca de que hay JS: sin ella el CSS de las animaciones de la galería
         dejaría las fotos ocultas si el navegador no ejecuta scripts. -->
    <script>document.documentElement.classList.add('js');</script>
    <title>Nosotros | Pupuseria Rinas</title>
  </head>
  <body class="page-nosotros">
    <?php include "./modules/views/layouts/header.php" ?>
    <?php include "./modules/views/principal/nosotros.php"?>
    <?php include "./modules/views/layouts/footer.php"?>
