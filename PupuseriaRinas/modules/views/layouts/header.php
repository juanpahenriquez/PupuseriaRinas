<?php
/**
 * Cabecera pública compartida por landing, menú, ubicación y nosotros.
 *
 * El estilo vive en custom.css (bloque "cabecera") para que las cuatro paginas
 * se vean igual: transparente sobre el hero y azul marino al scrollear.
 * El enlace de la pagina actual se marca con aria-current y el CSS lo pinta
 * en naranja, asi el visitante sabe donde esta en todo momento.
 */
$paginaActual = basename((string) ($_SERVER['PHP_SELF'] ?? ''));
$navRinas = [
    'menu.php'      => 'Menú',
    'ubicacion.php' => 'Ubicación',
    'nosotros.php'  => 'Nosotros',
];
?>

<a class="skip-rinas" href="#contenido">Saltar al contenido</a>

<header class="header-rinas fixed-top">
    <nav class="navbar navbar-expand-lg navbar-rinas" aria-label="Navegación principal">
        <div class="container">

            <a class="navbar-brand d-flex align-items-center" href="index.php">
                <img src="assets/img/logo.jpg" alt="Logo Pupusería Rinas" width="70" height="70" class="logo-rinas">
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal" aria-controls="menuPrincipal" aria-expanded="false" aria-label="Mostrar menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-center align-items-center" id="menuPrincipal">
                <div class="navbar-nav mx-auto gap-1 gap-lg-4 text-center align-items-center py-2 py-lg-0">
                    <?php foreach ($navRinas as $archivo => $texto): ?>
                        <a href="<?= $archivo ?>" class="nav-link"<?= $paginaActual === $archivo ? ' aria-current="page"' : '' ?>><?= $texto ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </nav>
</header>

<main id="contenido" tabindex="-1">
