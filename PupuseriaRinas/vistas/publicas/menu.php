<!-- ============================================================================
     Menú · mismo lenguaje que el landing (bloque LANDING de estilos.css):
     hero a sangre con parallax -> visor del flipbook enmarcado -> especialidades
     en recorte con parallax -> cinta -> CTA.

     Movimiento:
       data-parallax  -> lo resuelve landing.js (capa desplazada)
       data-reveal    -> entrada escalonada, tambien de landing.js
       data-split     -> partir el titular en lineas (assets/js/paginas.js)
     ========================================================================== -->

<!-- Barra de progreso de lectura (paginas.js) -->
<div class="px-progress" aria-hidden="true"><span class="px-progress-fill"></span></div>

<!-- ------------------------------------------------------------ 01 · hero -->
<section class="px-hero" id="inicio">
    <span class="px-hero-media" aria-hidden="true">
        <img src="assets/img/galeria/pupusas-comal.jpg?v=<?= LINK_CSS_VERSION ?>" alt="" data-parallax="9" fetchpriority="high" decoding="async">
        <span class="px-hero-veil"></span>
    </span>
    <span class="px-grain" aria-hidden="true"></span>

    <div class="px-hero-content">
        <div class="container">
            <span class="nl-eyebrow nl-eyebrow--claro" data-reveal>Carta Rinas</span>

            <h1 class="px-hero-title" data-split>Nuestro<br><span class="px-hero-accent">menú</span></h1>

            <p class="px-hero-lead" data-reveal data-reveal-delay="520">
                Pupusas recién hechas, café de la casa y licuados naturales. Pasa las
                páginas o míralo completo en una sola pantalla.
            </p>

            <div class="px-hero-cta" data-reveal data-reveal-delay="640">
                <a class="nl-btn nl-btn--naranja" href="#carta">Ver el menú</a>
                <a class="nl-btn nl-btn--ghost" href="llevar.php">Pedir para llevar</a>
            </div>

            <!-- Riel de datos: lo que se busca de un menú antes de pasar hoja. -->
            <div class="px-hero-rail" data-reveal data-reveal-delay="760">
                <div class="px-hero-cell">
                    <span class="px-hero-cell-label">Casa</span>
                    <span class="px-hero-cell-value">Pupusas · Café · Licuados</span>
                </div>
                <div class="px-hero-cell">
                    <span class="px-hero-cell-label">Cocina</span>
                    <span class="px-hero-cell-value">Al momento</span>
                </div>
                <div class="px-hero-cell">
                    <span class="px-hero-cell-label">Pedidos</span>
                    <span class="px-hero-cell-value">Tienda · Para llevar</span>
                </div>
            </div>
        </div>
    </div>

    <a class="px-hero-scroll" href="#carta" aria-label="Ir al menú">
        <span class="px-hero-scroll-line" aria-hidden="true"></span>
    </a>
</section>

<!-- -------------------------------------------------------- 02 · la carta -->
<!-- El flipbook se mantiene tal cual (es el PDF del local), pero entra dentro
     de un marco azul con barra de titulo y acciones: asi el bloque se lee como
     la portada del menu y no como un iframe suelto. El h2 sube con data-split. -->
<section class="px-section px-section--crema" id="carta" aria-labelledby="mnCartaTitulo">
    <div class="container">
        <div class="px-head" data-reveal>
            <span class="px-head-num">01 — La carta</span>
            <span class="px-head-line" aria-hidden="true"></span>
        </div>

        <div class="px-mn-viewer" data-reveal data-reveal-delay="60">
            <span class="px-grain" aria-hidden="true"></span>

            <div class="px-mn-bar">
                <div>
                    <h2 class="px-mn-bar-title" id="mnCartaTitulo">Menú Rinas Cafetería</h2>
                    <span class="px-mn-bar-sub">Pasa las hojas · Desliza con el dedo</span>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a class="nl-btn nl-btn--naranja" href="https://online.fliphtml5.com/hfctgt/vbdq/" target="_blank" rel="noopener">Abrir aparte</a>
                    <a class="nl-btn nl-btn--ghost" href="llevar.php">Pedir</a>
                </div>
            </div>

            <!-- La hoja conserva la proporción vertical del flipbook: el
                 alto sale del ancho con padding-top en %, no de una altura fija
                 que se descuadra al cambiar el tamaño de la pantalla. -->
            <div class="px-mn-sheet" style="padding-top: max(115%, 540px);">
                <iframe title="Menú Rinas Cafetería (PDF)"
                        src="https://online.fliphtml5.com/hfctgt/vbdq/"
                        seamless="seamless" scrolling="no" frameborder="0"
                        allowtransparency="true" allowfullscreen="true" loading="lazy"></iframe>
            </div>

            <div class="px-mn-foot">
                <p class="px-mn-foot-text">
                    ¿Prefieres que te lo expliquemos? Pide por WhatsApp y te
                    recomendamos lo que más se vende hoy.
                </p>
                <a class="nl-btn nl-btn--naranja" href="llevar.php">Hacer mi pedido</a>
            </div>
        </div>
    </div>
</section>

<!-- ------------------------------------------------ 03 · especialidades -- -->
<!-- Los recortes de producto flotan sobre el azul con parallax: es la foto del
     producto, no una fotografia, y por eso no se estiran a la caja. -->
<section class="px-section px-section--azul px-mn-especialidades" aria-labelledby="mnEspecialidadesTitulo">
    <span class="px-halo" aria-hidden="true"></span>
    <span class="px-grain" aria-hidden="true"></span>

    <div class="container">
        <div class="px-head px-head--claro" data-reveal>
            <span class="px-head-num">02 — Especialidades</span>
            <span class="px-head-line" aria-hidden="true"></span>
        </div>

        <div class="px-grid-2 mb-5">
            <div>
                <span class="nl-eyebrow nl-eyebrow--claro" data-reveal>Lo más pedido</span>
                <h2 class="nl-display" id="mnEspecialidadesTitulo" data-split data-reveal data-reveal-delay="60">
                    Cuatro para<br>empezar
                </h2>
            </div>
            <div>
                <p class="nl-lead" data-reveal data-reveal-delay="120">
                    De la plancha al vaso: pupusas de conocimiento antiguo con un giro
                    propio, y bebidas que acompañan el café sin esconderse.
                </p>
                <div class="d-flex gap-3 flex-wrap mt-4" data-reveal data-reveal-delay="180">
                    <a class="nl-btn nl-btn--naranja" href="llevar.php">Pedir para llevar</a>
                </div>
            </div>
        </div>

        <div class="px-products" data-reveal data-reveal-delay="60">
            <a class="px-product" href="llevar.php">
                <span class="px-product-ring" aria-hidden="true"></span>
                <span class="px-product-media" data-parallax="8" aria-hidden="true">
                    <img src="assets/img/pupa_camaron.png?v=<?= LINK_CSS_VERSION ?>" alt="" loading="lazy" decoding="async">
                </span>
                <h3 class="px-product-name">Pupusa de camarón</h3>
                <span class="px-product-tag">Sabor Mediterráneo</span>
            </a>

            <a class="px-product" href="llevar.php">
                <span class="px-product-ring" aria-hidden="true"></span>
                <span class="px-product-media" data-parallax="12" aria-hidden="true">
                    <img src="assets/img/pupa_chile.png?v=<?= LINK_CSS_VERSION ?>" alt="" loading="lazy" decoding="async">
                </span>
                <h3 class="px-product-name">Pupusa de chile</h3>
                <span class="px-product-tag">Con su punta</span>
            </a>

            <a class="px-product" href="llevar.php">
                <span class="px-product-ring" aria-hidden="true"></span>
                <span class="px-product-media" data-parallax="9" aria-hidden="true">
                    <img src="assets/img/pupa_pollo.png?v=<?= LINK_CSS_VERSION ?>" alt="" loading="lazy" decoding="async">
                </span>
                <h3 class="px-product-name">Pupusa de pollo</h3>
                <span class="px-product-tag">Clásica de la casa</span>
            </a>

            <a class="px-product" href="llevar.php">
                <span class="px-product-ring" aria-hidden="true"></span>
                <span class="px-product-media" data-parallax="13" aria-hidden="true">
                    <img src="assets/img/licuado.png?v=<?= LINK_CSS_VERSION ?>" alt="" loading="lazy" decoding="async">
                </span>
                <h3 class="px-product-name">Licuado clásico</h3>
                <span class="px-product-tag">De la casa</span>
            </a>
        </div>
    </div>
</section>

<!-- ------------------------------------------- cinta de palabras (común) -->
<div class="nl-marquee" aria-hidden="true">
    <div class="nl-marquee-track">
        <span>Pupusas al momento</span><span>&#183;</span><span>Café de la casa</span><span>&#183;</span>
        <span>Curtido y salsa</span><span>&#183;</span><span>Queso que estira</span><span>&#183;</span>
        <span>Licuados naturales</span><span>&#183;</span><span>Para llevar</span><span>&#183;</span>
        <span>Pupusas al momento</span><span>&#183;</span><span>Café de la casa</span><span>&#183;</span>
        <span>Curtido y salsa</span><span>&#183;</span><span>Queso que estira</span><span>&#183;</span>
        <span>Licuados naturales</span><span>&#183;</span><span>Para llevar</span><span>&#183;</span>
    </div>
</div>

<!-- -------------------------------------------------------------- 04 · CTA -->
<!-- Va en crema y no en azul: el bloque anterior (especialidades) ya es azul,
     y repetir azul sobre azul le quita el golpe visual al cierre. -->
<section class="px-cta px-cta--claro">
    <div class="container">
        <div class="px-cta-card">
            <span class="px-cta-brillo" aria-hidden="true"></span>
            <span class="px-grain" aria-hidden="true"></span>
            <span class="nl-eyebrow" data-reveal>¿Se te antojó?</span>
            <h2 class="px-cta-title" data-split data-reveal data-reveal-delay="60">Pide tu combo y lo tenemos listo</h2>
            <p class="px-cta-text" data-reveal data-reveal-delay="240">
                Elige tus pupusas, tu bebida y a qué hora lo recoges. Comal
                encendido, café recién hecho.
            </p>
            <div class="px-cta-acciones" data-reveal data-reveal-delay="320">
                <a class="nl-btn nl-btn--naranja" href="llevar.php">Pedir para llevar</a>
                <a class="nl-btn nl-btn--ghost" href="ubicacion.php">Cómo llegar</a>
            </div>
        </div>
    </div>
</section>

<script src="assets/js/landing.js?v=<?= LINK_CSS_VERSION ?>" defer></script>
<!-- Capa premium: titulares partidos en lineas y barra de progreso. -->
<script src="assets/js/paginas.js?v=<?= LINK_CSS_VERSION ?>" defer></script>