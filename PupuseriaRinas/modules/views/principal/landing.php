<?php
/**
 * Textos de las tarjetas de enlaces. Se leen del panel de Configuracion
 * para que la direccion y el menu no queden desactualizados.
 * Es fail-silent: si MySQL no responde se quedan los textos de abajo.
 */
$textoMenu      = 'Pupusas, bebidas y café de la casa';
$textoUbicacion = 'Paseo General Escalón #123, San Salvador';

require_once __DIR__ . '/../../../config/conexion.php';
$pdoLanding = db(false);
if ($pdoLanding) {
    try {
        $cfgLanding = [];
        foreach ($pdoLanding->query("SELECT clave, valor FROM configuracion") as $filaCfg) {
            $cfgLanding[$filaCfg['clave']] = (string) $filaCfg['valor'];
        }
        if (!empty($cfgLanding['direccion'])) {
            $textoUbicacion = $cfgLanding['direccion'];
        }
        $activosLanding = (int) $pdoLanding->query("SELECT COUNT(*) FROM productos WHERE estado = 'Activo'")->fetchColumn();
        if ($activosLanding > 0) {
            $textoMenu = $activosLanding . ($activosLanding === 1
                ? ' especialidad disponible'
                : ' especialidades disponibles');
        }
    } catch (Throwable $e) {
        // se conservan los textos por defecto
    }
}
?>
<!-- =====================================================================
     Landing Rinas ·-rediseño editorial
     Bloques: hero a sangre -> tres accesos -> galería a pantalla total ->
     producto destacado -> licuados -> equipo -> cinta de palabras.
     Clases con prefijo `nl-`: viven en assets/css/landing.css y no tocan
     las reglas que custom.css comparte con menu/ubicacion/nosotros.
     ===================================================================== -->

<!-- ------------------------------------------------------- 01 · hero --- -->
<section class="nl-hero" id="inicio">
    <div class="nl-hero-media">
        <video class="nl-hero-video" data-parallax="9" autoplay muted loop playsinline preload="metadata"
               poster="assets/img/galeria/pupusas-comal.jpg?v=<?= LINK_CSS_VERSION ?>">
            <source src="assets/video/8448183-hd_1920_1080_24fps.mp4" type="video/mp4">
        </video>
        <span class="nl-hero-veil" aria-hidden="true"></span>
    </div>

    <div class="nl-hero-content">
        <div class="container">
            <span class="nl-eyebrow nl-eyebrow--claro" data-reveal>Pupusería desde 2018</span>

            <h1 class="nl-hero-title">
                <span data-reveal>Café caliente</span>
                <span data-reveal data-reveal-delay="80">y pupusas</span>
                <span class="nl-hero-accent" data-reveal data-reveal-delay="160">Rinas</span>
            </h1>

            <p class="nl-hero-lead" data-reveal data-reveal-delay="240">
                Masa hecha al momento, café de la casa y el sabor de siempre.
                Recoge en tienda o pide para llevar.
            </p>

            <div class="nl-hero-cta" data-reveal data-reveal-delay="320">
                <a class="nl-btn nl-btn--naranja" href="https://wa.me/50370000000?" target="_blank" rel="noopener">Haz tu pedido</a>
            </div>
        </div>
    </div>

    <a class="nl-hero-scroll" href="#enlaces" aria-label="Ir a las secciones">
        <span class="nl-hero-scroll-line" aria-hidden="true"></span>
    </a>
</section>

<!-- ------------------------------------------- 02 · tres accesos ------- -->
<section class="nl-links" id="enlaces" aria-labelledby="nlEnlacesTitulo">
    <div class="container">
        <header class="nl-links-head">
            <h2 class="nl-title" id="nlEnlacesTitulo" data-reveal data-reveal-delay="60">
                Tres entradas,<br>un mismo comal
            </h2>
        </header>
    </div>

    <div class="nl-links-grid">
        <a class="nl-link" href="menu.php" data-reveal>
            <span class="nl-link-media" data-parallax="8" aria-hidden="true">
                <img src="assets/img/MENU LANDING.png" alt="" loading="lazy" decoding="async">
            </span>
            <span class="nl-link-veil" aria-hidden="true"></span>
            <span class="nl-link-body">
                <span class="nl-link-num">01</span>
                <span class="nl-link-title">Ver Menú</span>
                <span class="nl-link-text"><?= htmlspecialchars($textoMenu) ?></span>
                <span class="nl-link-go" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                </span>
            </span>
        </a>

        <a class="nl-link" href="ubicacion.php" data-reveal data-reveal-delay="90">
            <span class="nl-link-media" data-parallax="10" aria-hidden="true">
                <img src="assets/img/UBICACION LANDING.png" alt="" loading="lazy" decoding="async">
            </span>
            <span class="nl-link-veil" aria-hidden="true"></span>
            <span class="nl-link-body">
                <span class="nl-link-num">02</span>
                <span class="nl-link-title">Ubicación</span>
                <span class="nl-link-text"><?= htmlspecialchars($textoUbicacion) ?></span>
                <span class="nl-link-go" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                </span>
            </span>
        </a>

        <a class="nl-link" href="nosotros.php" data-reveal data-reveal-delay="180">
            <span class="nl-link-media" data-parallax="8" aria-hidden="true">
                <img src="assets/img/ACERCA DE NOSOTROS LANDING.png" alt="" loading="lazy" decoding="async">
            </span>
            <span class="nl-link-veil" aria-hidden="true"></span>
            <span class="nl-link-body">
                <span class="nl-link-num">03</span>
                <span class="nl-link-title">Nosotros</span>
                <span class="nl-link-text">Desde 2018 haciendo café y pupusas</span>
                <span class="nl-link-go" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                </span>
            </span>
        </a>
    </div>
</section>

<!-- ------------------------------------------ 03 · galería a pantalla --- -->
<section class="nl-gallery" id="galeria" aria-labelledby="nlGaleriaTitulo">

    <!-- Banner: 100svh, a sangre, sin radio ni sombra. El encabezado va dentro
         y superpuesto (ver .nl-gallery-intro), y el parallax corre sobre
         .nl-slide-media, sobredimensionada, para no destapar bordes. -->
    <div class="nl-gallery-banner">
        <header class="nl-gallery-intro">
            <span class="nl-eyebrow" data-reveal>Galería</span>
            <h2 class="nl-title" id="nlGaleriaTitulo" data-reveal data-reveal-delay="60">
                Directo desde<br>nuestro comal
            </h2>
        </header>

        <div id="nlGaleriaMain" class="splide nl-splide" aria-label="Galería de Pupusería Rinas">
            <div class="splide__track">
                <ul class="splide__list">
                    <li class="splide__slide">
                        <span class="nl-slide-media" data-parallax="8">
                            <img src="assets/img/galeria/pupusas-comal.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Pupusas recién salidas del comal">
                        </span>
                        <span class="nl-slide-cap">Recién hechas</span>
                    </li>
                    <li class="splide__slide">
                        <span class="nl-slide-media" data-parallax="8">
                            <img src="assets/img/galeria/pupusas-mesa.jpg" alt="El ambiente de la casa" loading="lazy">
                        </span>
                        <span class="nl-slide-cap">El ambiente</span>
                    </li>
                    <li class="splide__slide" data-fit="contain">
                        <span class="nl-slide-media" data-parallax="8">
                            <img src="assets/img/pupa_camaron.png" alt="Pupusa de camarón Sabor Mediterráneo" loading="lazy">
                        </span>
                        <span class="nl-slide-cap">Sabor Mediterráneo</span>
                    </li>
                    <li class="splide__slide" data-fit="contain">
                        <span class="nl-slide-media" data-parallax="8">
                            <img src="assets/img/pupa_chile.png" alt="Pupusa de chile" loading="lazy">
                        </span>
                        <span class="nl-slide-cap">Con su punta</span>
                    </li>
                    <li class="splide__slide" data-fit="contain">
                        <span class="nl-slide-media" data-parallax="8">
                            <img src="assets/img/licuado.png" alt="Licuado clásico con banana" loading="lazy">
                        </span>
                        <span class="nl-slide-cap">Licuado de la casa</span>
                    </li>
                    <li class="splide__slide">
                        <span class="nl-slide-media" data-parallax="8">
                            <img src="assets/img/galeria_1_600.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Ambiente Pupusería Rinas" loading="lazy">
                        </span>
                        <span class="nl-slide-cap">Pasa por Rinas</span>
                    </li>
                </ul>
            </div>
        </div>

        <div class="nl-gallery-bar">
            <div class="nl-gallery-bars">
                <button type="button" aria-label="Ir a la foto 1"><span></span></button>
                <button type="button" aria-label="Ir a la foto 2"><span></span></button>
                <button type="button" aria-label="Ir a la foto 3"><span></span></button>
                <button type="button" aria-label="Ir a la foto 4"><span></span></button>
                <button type="button" aria-label="Ir a la foto 5"><span></span></button>
                <button type="button" aria-label="Ir a la foto 6"><span></span></button>
            </div>
            <span class="nl-gallery-count"><b id="nlGaleriaActual">01</b> / <span id="nlGaleriaTotal">06</span></span>
        </div>
    </div>

    <!-- Banda de miniaturas a sangre: sin .container, para que las seis
         repartan todo el ancho del bloque crema. -->
    <div class="nl-gallery-thumbs-wrap">
        <div id="nlGaleriaThumbs" class="splide nl-thumbs" aria-label="Ir a la imagen de la galería">
            <div class="splide__track">
                <ul class="splide__list">
                    <li class="splide__slide"><img src="assets/img/galeria/pupusas-comal.jpg?v=<?= LINK_CSS_VERSION ?>" alt=""></li>
                    <li class="splide__slide"><img src="assets/img/galeria/pupusas-mesa.jpg" alt="" loading="lazy"></li>
                    <li class="splide__slide"><img src="assets/img/pupa_camaron.png" alt="" loading="lazy"></li>
                    <li class="splide__slide"><img src="assets/img/pupa_chile.png" alt="" loading="lazy"></li>
                    <li class="splide__slide"><img src="assets/img/licuado.png" alt="" loading="lazy"></li>
                    <li class="splide__slide"><img src="assets/img/galeria_1_600.jpg?v=<?= LINK_CSS_VERSION ?>" alt="" loading="lazy"></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<!-- --------------------------------------------- 04 · producto -------- -->
<section class="nl-feature" id="especialidad" aria-labelledby="nlEspecialidadTitulo">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-12 col-lg-6 order-lg-1">
                <div class="nl-feature-visual">
                    <span class="nl-feature-ring" aria-hidden="true"></span>
                    <img class="nl-feature-img" data-parallax="7"
                         src="assets/img/pupa_camaron.png"
                         alt="Pupusa de camarón Sabor Mediterráneo" loading="lazy">
                </div>
            </div>

            <div class="col-12 col-lg-6 order-lg-2">
                <span class="nl-eyebrow nl-eyebrow--claro" data-reveal>Sabor Mediterráneo</span>
                <h2 class="nl-display" id="nlEspecialidadTitulo" data-reveal data-reveal-delay="60">
                    Pupusa<br>de Camarón
                </h2>
                <p class="nl-lead" data-reveal data-reveal-delay="120">
                    Camarón jugoso y queso fundido en tortilla gruesa dorada a la plancha.
                    Un giro costeño con alma salvadoreña: marisco fresco, queso cremoso
                    y el sazón de la casa, servida siempre caliente con curtido y salsa.
                </p>

                <div class="nl-feature-buy">
                    <div>
                        <span class="nl-label">Precio</span>
                        <span class="nl-price" id="nlPrecio">$1.50</span>
                    </div>
                    <div>
                        <span class="nl-label">Unidades</span>
                        <div class="nl-sizes" id="nlTamanos">
                            <button class="is-active" type="button" data-price="1.50">1</button>
                            <button type="button" data-price="4.50">3</button>
                            <button type="button" data-price="9.00">6</button>
                            <button type="button" data-price="18.00">12</button>
                        </div>
                    </div>
                </div>

                <div class="nl-feature-cta">
                    <a class="nl-btn nl-btn--naranja" href="llevar.php">Pide para llevar</a>
                    <button class="nl-icon-btn" type="button" data-fav aria-pressed="false"
                            aria-label="Marcar como favorito">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-4.5-2.8-7-6.5A4.5 4.5 0 0 1 12 7a4.5 4.5 0 0 1 7 7.5c-2.5 3.7-7 6.5-7 6.5z"/></svg>
                    </button>
                    <a class="nl-btn nl-btn--linea" href="menu.php">Ver menú</a>
                </div>

                <div class="nl-feature-meta">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                        Listo en 15 min
                    </span>
                    <span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ----------------------------------------------- 05 · licuados ------- -->
<section class="nl-smoothies" id="bebidas" aria-labelledby="nlLicuadoTitulo">
    <div class="container">
        <div class="nl-smoothies-grid">
            <div class="nl-smoothies-copy">
                <span class="nl-eyebrow" data-reveal>Bebidas de la casa</span>
                <h2 class="nl-display" id="nlLicuadoTitulo" data-reveal data-reveal-delay="60">Licuados</h2>
                <p class="nl-lead" data-reveal data-reveal-delay="120">
                    Una forma deliciosa de acompañar tus pupusas.
                </p>

                <ul class="nl-tags" aria-label="Ingredientes destacados" data-reveal data-reveal-delay="180">
                    <li>Frutas</li>
                    <li>Leche</li>
                    <li>Naturales</li>
                </ul>

                <a class="nl-btn nl-btn--azul" href="llevar.php" data-reveal data-reveal-delay="240">
                    Pide para llevar
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                </a>
            </div>

            <div class="nl-smoothies-visual">
                <span class="nl-disc" data-parallax="12" aria-hidden="true"></span>

                <div id="nlLicuadoCarousel" class="carousel slide nl-licuado-carousel"
                     data-bs-ride="carousel" data-bs-interval="4200" aria-label="Tipos de licuado">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img class="licuado-img" src="assets/img/licuado.png" alt="Licuado clásico con banana" loading="lazy">
                        </div>
                        <div class="carousel-item">
                            <img class="licuado-img" src="assets/img/fresa.png" alt="Licuado de fresa" loading="lazy">
                        </div>
                        <div class="carousel-item">
                            <img class="licuado-img" src="assets/img/oreo.png" alt="Licuado de Oreo" loading="lazy">
                        </div>
                    </div>

                    <button class="nl-carousel-arrow nl-carousel-arrow--prev" type="button"
                            data-bs-target="#nlLicuadoCarousel" data-bs-slide="prev" aria-label="Licuado anterior">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="M12 19l7-7-7-7"/></svg>
                    </button>
                    <button class="nl-carousel-arrow nl-carousel-arrow--next" type="button"
                            data-bs-target="#nlLicuadoCarousel" data-bs-slide="next" aria-label="Licuado siguiente">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                    </button>

                    <div class="nl-licuado-dots">
                        <button class="active" type="button" data-bs-target="#nlLicuadoCarousel" data-bs-slide-to="0" aria-label="Licuado clásico" aria-current="true"></button>
                        <button type="button" data-bs-target="#nlLicuadoCarousel" data-bs-slide-to="1" aria-label="Licuado de fresa"></button>
                        <button type="button" data-bs-target="#nlLicuadoCarousel" data-bs-slide-to="2" aria-label="Licuado de Oreo"></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- --------------------------------------------------- 06 · equipo ------ -->
<section class="nl-join" id="equipo" aria-labelledby="nlEquipoTitulo">
    <div class="container">
        <div class="nl-join-grid">
            <div class="nl-join-intro">
                <span class="nl-eyebrow nl-eyebrow--claro" data-reveal>Únete al equipo</span>
                <h2 class="nl-display" id="nlEquipoTitulo" data-reveal data-reveal-delay="60">
                    Trabaja<br>con nosotros
                </h2>
                <p class="nl-lead" data-reveal data-reveal-delay="120">
                    ¿Te apasiona cocinar, servir o llevar nuestro sabor?
                    Encuentra tu lugar en el equipo Rinas.
                </p>
                <ul class="nl-tags nl-tags--ghost" data-reveal data-reveal-delay="180">
                    <li>Cocina</li>
                    <li>Servicio</li>
                    <li>Reparto</li>
                </ul>
            </div>

            <ul class="nl-join-list" data-reveal data-reveal-delay="120">
                <li>
                    <a class="nl-role" target="_blank" rel="noopener"
                       href="https://wa.me/50370000000?text=Hola%20quiero%20aplicar%20para%20Cocina%20en%20Rinas"
                       aria-label="Postular a Cocina por WhatsApp">
                        <span class="nl-role-num">01</span>
                        <span>
                            <span class="nl-role-name">Cocina</span>
                            <span class="nl-role-desc">Prepara pupusas con sazón casero y forma parte del corazón de Rinas.</span>
                        </span>
                        <span class="nl-role-go" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                </li>
                <li>
                    <a class="nl-role" target="_blank" rel="noopener"
                       href="https://wa.me/50370000000?text=Hola%20quiero%20aplicar%20para%20Servicio%20en%20Rinas"
                       aria-label="Postular a Servicio por WhatsApp">
                        <span class="nl-role-num">02</span>
                        <span>
                            <span class="nl-role-name">Servicio</span>
                            <span class="nl-role-desc">Ofrece atención cercana y ágil para que cada visita se sienta como en casa.</span>
                        </span>
                        <span class="nl-role-go" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                </li>
                <li>
                    <a class="nl-role" target="_blank" rel="noopener"
                       href="https://wa.me/50370000000?text=Hola%20quiero%20aplicar%20para%20Reparto%20en%20Rinas"
                       aria-label="Postular a Reparto por WhatsApp">
                        <span class="nl-role-num">03</span>
                        <span>
                            <span class="nl-role-name">Reparto</span>
                            <span class="nl-role-desc">Lleva el sabor Rinas a domicilio con puntualidad y conocimiento de la ruta.</span>
                        </span>
                        <span class="nl-role-go" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</section>

<!-- -------------------------------------------- cinta de palabras -------- -->
<!-- Decorativa: aria-hidden para que no se lea en voz alta -->
<div class="nl-marquee" aria-hidden="true">
    <div class="nl-marquee-track">
        <span>Pupusas</span><span>&#183;</span><span>Café de la casa</span><span>&#183;</span>
        <span>Comal</span><span>&#183;</span><span>Curtido</span><span>&#183;</span>
        <span>Salsa</span><span>&#183;</span><span>Caldo</span><span>&#183;</span>
        <span>Pupusas</span><span>&#183;</span><span>Café de la casa</span><span>&#183;</span>
        <span>Comal</span><span>&#183;</span><span>Curtido</span><span>&#183;</span>
        <span>Salsa</span><span>&#183;</span><span>Caldo</span><span>&#183;</span>
    </div>
</div>

<script src="<?= LINK_SPLIDE_JS ?>"></script>
<!-- Versionado igual que el CSS: sin esto el navegador puede servir un
     landing.js viejo desde cache y la pagina se queda sin animaciones. -->
<script src="assets/js/landing.js?v=<?= LINK_CSS_VERSION ?>" defer></script>