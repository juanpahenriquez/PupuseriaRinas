<?php require_once __DIR__ . '/../../app/inicio.php'; ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $page_title ?? 'Admin'; ?> — Pupusería Rinas</title>
<script>
/* Sidebar: se aplica la preferencia guardada ANTES de pintar el <body>,
   así el rail no aparece expandido un instante al cambiar de página.
   En móvil el rail no aplica (ahí el menú es un cajón), por eso se
   comprueba el ancho: sin esto los textos del menú quedarían ocultos. */
(function () {
  try {
    if (localStorage.getItem('rinas_admin_sidebar') === 'rail' &&
        window.matchMedia('(min-width: 992px)').matches) {
      document.documentElement.classList.add('admin-menu-cerrado');
    }
  } catch (e) { /* modo privado o storage bloqueado: se ignora */ }
})();
</script>
<link href="<?= LINK_BOOTSTRAP_CSS ?>" rel="stylesheet">
<!-- Una sola hoja para todo el frontend (tokens + panel). Antes eran
     custom.css y admin.css, cada una con su propio ?v=; ahora todo se
     versiona con LINK_CSS_VERSION. -->
<link href="../assets/css/estilos.css?v=<?= LINK_CSS_VERSION ?>" rel="stylesheet">
<link href="<?= LINK_FONTAWESOME_CSS ?>" rel="stylesheet">
<script src="<?= LINK_BOOTSTRAP_JS ?>"></script>
