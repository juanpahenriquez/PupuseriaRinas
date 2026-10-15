<!-- ============================================================================
     Nosotros · mismo lenguaje que el landing (bloque LANDING de estilos.css):
     hero a sangre con parallax -> franja de foto -> historia en dos columnas ->
     manifiesto sobre azul con fondo en parallax -> galeria -> CTA -> cinta.

     Movimiento:
       data-parallax  -> lo resuelve landing.js (capa desplazada)
       data-reveal    -> entrada escalonada, tambien de landing.js
       data-split     -> partir el titular en lineas (assets/js/paginas.js)
       data-count     -> cifras que cuentan al entrar en pantalla
     ========================================================================== -->

<!-- Barra de progreso: se inyecta en el body, pero el elemento vive aqui para
     que la pagina siga siendo autonoma si se incluye el partial suelto. -->
<div class="px-progress" aria-hidden="true"><span class="px-progress-fill"></span></div>

<!-- ------------------------------------------------------------ 01 · hero -->
<section class="px-hero" id="inicio">
    <span class="px-hero-media" aria-hidden="true">
        <img src="assets/img/pexels-allanglezg-30005123.jpg" alt="" data-parallax="9" fetchpriority="high" decoding="async">
        <span class="px-hero-veil"></span>
    </span>
    <span class="px-grain" aria-hidden="true"></span>

    <div class="px-hero-content">
        <div class="container">
            <span class="nl-eyebrow nl-eyebrow--claro" data-reveal>Pupusería desde 2018</span>

            <h1 class="px-hero-title" data-split>Sobre<br>nuestra <span class="px-hero-accent">casa</span></h1>

            <p class="px-hero-lead" data-reveal data-reveal-delay="520">
                Una cocina pequeña, un comal que nunca descansa y el mismo sazón de
                siempre. Esta es la historia de Rinas.
            </p>

            <div class="px-hero-cta" data-reveal data-reveal-delay="640">
                <a class="nl-btn nl-btn--naranja" href="menu.php">Ver Menú</a>
                <a class="nl-btn nl-btn--ghost" href="ubicacion.php">Ubícate aquí</a>
            </div>

            <!-- Riel de datos: los tres minimos quetodo el mundo busca. -->
            <div class="px-hero-rail" data-reveal data-reveal-delay="760">
                <div class="px-hero-cell">
                    <span class="px-hero-cell-label">Fundada</span>
                    <span class="px-hero-cell-value">Desde 2018</span>
                </div>
                <div class="px-hero-cell">
                    <span class="px-hero-cell-label">Reputación</span>
                    <span class="px-hero-cell-value">4.9 ★ · 50k clientes</span>
                </div>
                <div class="px-hero-cell">
                    <span class="px-hero-cell-label">Cocina</span>
                    <span class="px-hero-cell-value">Casera · Al momento</span>
                </div>
            </div>
        </div>
    </div>

    <a class="px-hero-scroll" href="#historia" aria-label="Ir al contenido">
        <span class="px-hero-scroll-line" aria-hidden="true"></span>
    </a>
</section>

<!-- --------------------------------------------- 02 · franja de foto ---- -->
<section class="px-strip" aria-label="Del comal a la mesa">
    <span class="px-strip-media" data-parallax="10" aria-hidden="true">
        <img src="assets/img/galeria/pexels-comal-manos.jpg?v=<?= LINK_CSS_VERSION ?>" alt="" loading="lazy" decoding="async">
    </span>
    <span class="px-strip-veil" aria-hidden="true"></span>
    <span class="px-grain px-grain--fuerte" aria-hidden="true"></span>
    <div class="px-strip-caption">
        <h2 class="px-strip-title" data-split data-reveal>Masa hecha a mano, todos los días</h2>
        <span class="px-strip-num" aria-hidden="true">01 — 04</span>
    </div>
</section>

<!-- -------------------------------------------------------- 03 · historia - -->
<section class="px-section px-section--crema" id="historia" aria-labelledby="nsHistoriaTitulo">
    <div class="container">
        <!-- Cabecera de bloque: numero + filete que cruza el ancho. -->
        <div class="px-head" data-reveal>
            <span class="px-head-num">01 — Historia</span>
            <span class="px-head-line" aria-hidden="true"></span>
        </div>

        <div class="px-grid-2">
            <div>
                <span class="nl-eyebrow" data-reveal>Nuestra historia</span>
                <h2 class="nl-display" id="nsHistoriaTitulo" data-split data-reveal data-reveal-delay="60">
                    Todo empezó<br>con un comal
                </h2>

                <div class="px-prose" data-reveal data-reveal-delay="120">
                    <p>
                        Empezamos con un comal pequeño y muchas ganas de compartir el sabor
                        de casa. Lo que era un fin de semana de recetas familiares se fue
                        convirtiendo en el lugar donde el barrio se reúne a desayunar, a
                        almorzar y a tomarse el café de la tarde.
                    </p>
                    <p>
                        Con el tiempo la plancha se amplió, el café se volvió de la casa y
                        la carta creció sin perder lo esencial: maíz recién molido, queso
                        que se estira, curtido fresco, salsa con el punto justo. Cada pupusa
                        se prepara por encargo, porque creemos que el sabor bueno no se
                        apura, se cuida.
                    </p>
                    <p>
                        Hoy Rinas es mucho más que una pupusería: es un espacio para
                        encontrarse, para llevarse algo rico y para sentir, en cada bocado,
                        el mismo sazón de siempre. Gracias por ser parte de esta historia.
                    </p>
                </div>

                <div class="d-flex gap-3 flex-wrap mt-4" data-reveal data-reveal-delay="180">
                    <a class="nl-btn nl-btn--naranja" href="menu.php">Ver Menú</a>
                    <a class="nl-btn nl-btn--linea" href="ubicacion.php">Ubícate aquí</a>
                </div>
            </div>

            <!-- Collage: la foto grande al lado y la chica desfasada hacia
                 abajo. Las dos capas se desplazan a velocidad distinta. -->
            <div class="px-ns-collage">
                <figure class="px-ns-collage-main">
                    <span class="px-ns-collage-media" data-parallax="8" aria-hidden="true">
                        <img src="assets/img/galeria/pexels-comal-pupusa-guacamole.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Pupusa de queso con guacamole y curtido" loading="lazy" decoding="async">
                    </span>
                </figure>
                <figure class="px-ns-collage-side">
                    <span class="px-ns-collage-media" data-parallax="13" aria-hidden="true">
                        <img src="assets/img/galeria/pexels-cafe-latte.jpg" alt="Café latte de la casa" loading="lazy" decoding="async">
                    </span>
                </figure>
            </div>
        </div>

        <!-- Las cifras cuentan de 0 al valor al entrar en pantalla; el sufijo
             va aparte para que el JS no tenga que interpretarlo. -->
        <div class="px-stats" data-reveal data-reveal-delay="120">
            <div class="px-stat">
                <strong data-count="8" data-suffix="+ años">8+ años</strong>
                <span>de sabor casero</span>
            </div>
            <div class="px-stat">
                <strong data-count="50" data-suffix="k">50k</strong>
                <span>clientes felices</span>
            </div>
            <div class="px-stat">
                <strong data-count="4.9" data-suffix=" ★">4.9 ★</strong>
                <span>promedio de reseñas</span>
            </div>
        </div>
    </div>
</section>

<!-- ---------------------------------------------------- 04 · manifiesto --- -->
<section class="px-section px-section--azul px-ns-manifiesto" aria-labelledby="nsManifiestoTitulo">
    <span class="px-ns-bg" aria-hidden="true">
        <span class="px-ns-bg-media" data-parallax="7">
            <img src="assets/img/pexels-noevillaltaphotography-9216207.jpg" alt="" loading="lazy" decoding="async">
        </span>
        <span class="px-ns-bg-veil"></span>
    </span>
    <span class="px-halo" aria-hidden="true"></span>
    <span class="px-grain" aria-hidden="true"></span>

    <div class="container">
        <div class="px-head px-head--claro" data-reveal>
            <span class="px-head-num">02 — Manifiesto</span>
            <span class="px-head-line" aria-hidden="true"></span>
        </div>

        <div class="px-grid-2">
            <div>
                <span class="nl-eyebrow nl-eyebrow--claro" data-reveal>Propósito y compromiso</span>
                <h2 class="nl-display" id="nsManifiestoTitulo" data-split data-reveal data-reveal-delay="60">
                    Misión<br>y visión
                </h2>
                <p class="nl-lead" data-reveal data-reveal-delay="120">
                    De un comal pequeño a tu mesa: el mismo cuidado en cada pupusa que
                    sale de la plancha.
                </p>
                <blockquote class="px-ns-quote mt-4" data-reveal data-reveal-delay="180">
                    “El sabor bueno <span>no se apura</span>, se cuida.”
                </blockquote>
            </div>

            <div class="px-cards px-cards--claro" data-reveal data-reveal-delay="120">
                <div class="px-card">
                    <span class="px-card-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-4.5-7-10a7 7 0 0 1 14 0c0 5.5-7 10-7 10z"/><circle cx="12" cy="11" r="2.5"/></svg>
                    </span>
                    <span class="px-card-num">01 — Misión</span>
                    <h3 class="px-card-title">Servir con alma de casa</h3>
                    <p class="px-card-text">
                        Servir cada día pupusas y café con sazón casero, en un ambiente
                        cercano y alegre, para que cada visita —en tienda o para llevar—
                        se sienta como en casa.
                    </p>
                </div>

                <div class="px-card">
                    <span class="px-card-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6-10-6-10-6z"/><circle cx="12" cy="12" r="2.5"/></svg>
                    </span>
                    <span class="px-card-num">02 — Visión</span>
                    <h3 class="px-card-title">El sabor que nos representa</h3>
                    <p class="px-card-text">
                        Ser la pupusería y cafetería de referencia en el Salvador,
                        reconocida por su sabor, su calidad y su gente, sin perder nunca
                        el toque artesanal de siempre.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ------------------------------------------ 05 · franja de foto + galería -->
<section class="px-strip" aria-label="La casa por dentro">
    <span class="px-strip-media" data-parallax="9" aria-hidden="true">
        <img src="assets/img/galeria/pexels-cafe-interior.jpg?v=<?= LINK_CSS_VERSION ?>" alt="" loading="lazy" decoding="async">
    </span>
    <span class="px-strip-veil" aria-hidden="true"></span>
    <span class="px-grain px-grain--fuerte" aria-hidden="true"></span>
    <div class="px-strip-caption">
        <h2 class="px-strip-title" data-split data-reveal>Un lugar para quedarse</h2>
        <span class="px-strip-num" aria-hidden="true">02 — 04</span>
    </div>
</section>

<!-- ============================================================================
     Galería · bloque oscuro al estilo del landing: una foto grande que manda y
     una retícula editorial alrededor. Todo se amplía en el lightbox de abajo.
     ============================================================================ -->
<section class="galeria-rinas" id="galeria" aria-labelledby="galeriaTitulo">
    <span class="galeria-filete" aria-hidden="true"></span>

    <div class="container">
        <header class="galeria-head">
            <div class="galeria-head-copy">
                <span class="trabaja-eyebrow galeria-eyebrow">Así se ve Rinas</span>
                <h2 class="galeria-titulo" id="galeriaTitulo">Galería</h2>
                <p class="galeria-lead">Recién salidas del comal, el ambiente de la casa y las bebidas que acompañan. Filtra por categoría o toca una foto para verla en grande.</p>
            </div>
            <p class="galeria-head-meta"><b id="galeriaConteo">12</b> fotos</p>
        </header>

        <div class="galeria-filtros" role="group" aria-label="Filtrar la galería por categoría">
            <button class="galeria-chip is-active" type="button" data-filtro="todo" aria-pressed="true">Todo</button>
            <button class="galeria-chip" type="button" data-filtro="comal" aria-pressed="false">Del comal</button>
            <button class="galeria-chip" type="button" data-filtro="bebidas" aria-pressed="false">Bebidas</button>
            <button class="galeria-chip" type="button" data-filtro="lugar" aria-pressed="false">El lugar</button>
        </div>

        <div class="galeria-grid">
            <figure class="galeria-card galeria-card--xl" data-cat="comal">
                <img src="assets/img/galeria/pexels-comal-pupusa-guacamole.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Pupusa de queso con guacamole y curtido" loading="lazy" decoding="async">
                <span class="galeria-card-zoom" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                </span>
                <figcaption class="galeria-card-pie">
                    <span class="galeria-card-num">01</span>
                    <span class="galeria-card-titulo">Pupusa con guacamole</span>
                </figcaption>
            </figure>

            <figure class="galeria-card" data-cat="comal">
                <img src="assets/img/galeria/pexels-comal-vapor.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Pupusas cocinándose al vapor en la plancha" loading="lazy" decoding="async">
                <span class="galeria-card-zoom" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                </span>
                <figcaption class="galeria-card-pie">
                    <span class="galeria-card-num">02</span>
                    <span class="galeria-card-titulo">Al vapor en la plancha</span>
                </figcaption>
            </figure>

            <figure class="galeria-card" data-cat="comal">
                <img src="assets/img/galeria/pexels-comal-manos.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Manos moldeando tortillas a mano sobre el comal" loading="lazy" decoding="async">
                <span class="galeria-card-zoom" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                </span>
                <figcaption class="galeria-card-pie">
                    <span class="galeria-card-num">03</span>
                    <span class="galeria-card-titulo">Masa hecha a mano</span>
                </figcaption>
            </figure>

            <figure class="galeria-card" data-cat="comal">
                <img src="assets/img/galeria/pexels-comal-primer-plano.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Pupusa de queso recién hecha a punto de partirse" loading="lazy" decoding="async">
                <span class="galeria-card-zoom" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                </span>
                <figcaption class="galeria-card-pie">
                    <span class="galeria-card-num">04</span>
                    <span class="galeria-card-titulo">Queso que estira</span>
                </figcaption>
            </figure>

            <figure class="galeria-card" data-cat="comal">
                <img src="assets/img/galeria/pexels-mesa-desayuno.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Mesa servida con desayuno junto a la ventana" loading="lazy" decoding="async">
                <span class="galeria-card-zoom" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                </span>
                <figcaption class="galeria-card-pie">
                    <span class="galeria-card-num">05</span>
                    <span class="galeria-card-titulo">La mesa servida</span>
                </figcaption>
            </figure>

            <figure class="galeria-card" data-cat="bebidas">
                <img src="assets/img/galeria/pexels-licuado-fresa.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Tres licuados de fresa con fresa de adorno" loading="lazy" decoding="async">
                <span class="galeria-card-zoom" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                </span>
                <figcaption class="galeria-card-pie">
                    <span class="galeria-card-num">06</span>
                    <span class="galeria-card-titulo">Licuado de fresa</span>
                </figcaption>
            </figure>

            <figure class="galeria-card" data-cat="bebidas">
                <img src="assets/img/galeria/pexels-licuados-frutales.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Licuados frutales de colores en vasos" loading="lazy" decoding="async">
                <span class="galeria-card-zoom" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                </span>
                <figcaption class="galeria-card-pie">
                    <span class="galeria-card-num">07</span>
                    <span class="galeria-card-titulo">Licuados de colores</span>
                </figcaption>
            </figure>

            <figure class="galeria-card" data-cat="bebidas">
                <img src="assets/img/galeria/pexels-cafe-latte.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Café latte con arte de leche y pastel" loading="lazy" decoding="async">
                <span class="galeria-card-zoom" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                </span>
                <figcaption class="galeria-card-pie">
                    <span class="galeria-card-num">08</span>
                    <span class="galeria-card-titulo">Café de la casa</span>
                </figcaption>
            </figure>

            <figure class="galeria-card galeria-card--ancha" data-cat="lugar">
                <img src="assets/img/galeria/pexels-cafe-interior.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Interior de cafetería con anaqueles de café" loading="lazy" decoding="async">
                <span class="galeria-card-zoom" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                </span>
                <figcaption class="galeria-card-pie">
                    <span class="galeria-card-num">09</span>
                    <span class="galeria-card-titulo">Nuestra esquina</span>
                </figcaption>
            </figure>

            <figure class="galeria-card" data-cat="lugar">
                <img src="assets/img/galeria/pexels-cafe-sala.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Sala acogedora con sillones de una cafetería" loading="lazy" decoding="async">
                <span class="galeria-card-zoom" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                </span>
                <figcaption class="galeria-card-pie">
                    <span class="galeria-card-num">10</span>
                    <span class="galeria-card-titulo">Un lugar para quedarse</span>
                </figcaption>
            </figure>

            <figure class="galeria-card" data-cat="lugar">
                <img src="assets/img/galeria/pexels-cafe-cliente.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Cliente tomándose una taza de café" loading="lazy" decoding="async">
                <span class="galeria-card-zoom" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                </span>
                <figcaption class="galeria-card-pie">
                    <span class="galeria-card-num">11</span>
                    <span class="galeria-card-titulo">El ritual del café</span>
                </figcaption>
            </figure>

            <figure class="galeria-card" data-cat="comal">
                <img src="assets/img/galeria/pexels-desayuno-salvadoreno.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Desayuno salvadoreño con huevos, aguacate y jugo natural" loading="lazy" decoding="async">
                <span class="galeria-card-zoom" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/><path d="M11 8v6"/><path d="M8 11h6"/></svg>
                </span>
                <figcaption class="galeria-card-pie">
                    <span class="galeria-card-num">12</span>
                    <span class="galeria-card-titulo">Desayuno de la casa</span>
                </figcaption>
            </figure>
        </div>

        <p class="galeria-vacio" hidden>Todavía no hay fotos en esta categoría.</p>
    </div>
</section>

<!-- -------------------------------------------------------------- 06 · CTA -->
<section class="px-cta">
    <div class="container">
        <div class="px-cta-card">
            <span class="px-cta-brillo" aria-hidden="true"></span>
            <span class="px-cta-vineta" aria-hidden="true"></span>
            <span class="px-grain px-grain--fuerte" aria-hidden="true"></span>
            <span class="nl-eyebrow" data-reveal>¿Se te antojó?</span>
            <h2 class="px-cta-title" data-split data-reveal data-reveal-delay="60">Comal caliente, café recién hecho</h2>
            <p class="px-cta-text" data-reveal data-reveal-delay="240">
                Pasa por Rinas o escríbenos y encargamos tu pedido. Te esperamos con
                el mismo sabor de casa.
            </p>
            <div class="px-cta-acciones" data-reveal data-reveal-delay="320">
                <a class="nl-btn nl-btn--naranja" href="menu.php">Ver el menú</a>
                <a class="nl-btn nl-btn--ghost" href="ubicacion.php">Cómo llegar</a>
            </div>
        </div>
    </div>
</section>

<!-- ------------------------------------------- cinta de palabras (común) -->
<div class="nl-marquee" aria-hidden="true">
    <div class="nl-marquee-track">
        <span>Comal</span><span>&#183;</span><span>Café de la casa</span><span>&#183;</span>
        <span>Masa hecha al momento</span><span>&#183;</span><span>Curtido</span><span>&#183;</span>
        <span>Salsa</span><span>&#183;</span><span>Pupusas</span><span>&#183;</span>
        <span>Comal</span><span>&#183;</span><span>Café de la casa</span><span>&#183;</span>
        <span>Masa hecha al momento</span><span>&#183;</span><span>Curtido</span><span>&#183;</span>
        <span>Salsa</span><span>&#183;</span><span>Pupusas</span><span>&#183;</span>
    </div>
</div>

<!-- Lightbox para ampliar las fotos de la galería -->
<div class="lightbox-rinas" id="lightboxRinas" hidden>
    <button class="lightbox-cerrar" type="button" aria-label="Cerrar imagen ampliada">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
    <button class="lightbox-flecha lightbox-prev" type="button" aria-label="Imagen anterior">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 5l-7 7 7 7"/></svg>
    </button>
    <figure class="lightbox-figura">
        <img id="lightboxImg" src="" alt="">
        <figcaption id="lightboxPie"></figcaption>
    </figure>
    <button class="lightbox-flecha lightbox-next" type="button" aria-label="Imagen siguiente">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 5l7 7-7 7"/></svg>
    </button>
</div>

<script>
/* Lightbox: recorre SOLO las fotos que están a la vista, así al filtrar por
   categoría las flechas no se saltan a las fotos ocultas. */
(function () {
  var overlay = document.getElementById('lightboxRinas');
  if (!overlay) return;
  var img = document.getElementById('lightboxImg');
  var pie = document.getElementById('lightboxPie');
  var galeria = [];      /* fotos visibles ahora mismo */
  var idx = 0;
  var focoPrevio = null;

  function texto(fig) {
    var cap = fig.querySelector('figcaption');
    if (!cap) return 'imagen de la galería';
    /* El título va en su propio span para que el número (01, 02…) no se lea
       como parte del pie de foto. */
    var titulo = cap.querySelector('.galeria-card-titulo');
    return (titulo ? titulo.textContent : cap.textContent).trim();
  }

  /* Recalcula la lista visible cada vez que se abre o se navega. */
  function recalcular() {
    galeria = Array.prototype.slice.call(document.querySelectorAll('.galeria-card:not(.is-hidden)'));
  }

  function pintar() {
    var fig = galeria[idx];
    var foto = fig.querySelector('img');
    img.src = foto.dataset.full || foto.src;
    img.alt = foto.alt;
    /* La foto vuelve a entrar con fundido: al cambiar el src el navegador
       reutiliza el mismo <img> y el cambio pega un salto seco. */
    pie.innerHTML = '';
    var contador = document.createElement('span');
    contador.className = 'lightbox-contador';
    contador.textContent = ('0' + (idx + 1)).slice(-2) + ' / ' + ('0' + galeria.length).slice(-2);
    pie.appendChild(contador);
    pie.appendChild(document.createTextNode(texto(fig)));
  }

  function abrir(i) {
    recalcular();
    if (!galeria.length) return;
    idx = (i + galeria.length) % galeria.length;
    pintar();
    focoPrevio = document.activeElement;
    overlay.hidden = false;
    document.body.style.overflow = 'hidden';
    overlay.querySelector('.lightbox-cerrar').focus();
  }

  /* Abre justo la foto que se tocó, no la primera de la lista. */
  function abrirElemento(fig) {
    recalcular();
    var i = galeria.indexOf(fig);
    abrir(i < 0 ? 0 : i);
  }

  function cerrar() {
    overlay.hidden = true;
    document.body.style.overflow = '';
    img.src = '';
    if (focoPrevio) focoPrevio.focus();
  }

  function mover(d) { abrir(idx + d); }

  document.querySelectorAll('.galeria-card').forEach(function (fig) {
    fig.setAttribute('tabindex', '0');
    fig.setAttribute('role', 'button');
    fig.setAttribute('aria-label', 'Ampliar: ' + texto(fig));
    fig.addEventListener('click', function () { abrirElemento(fig); });
    fig.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); abrirElemento(fig); }
    });
  });

  overlay.addEventListener('click', function (e) { if (e.target === overlay) cerrar(); });
  overlay.querySelector('.lightbox-cerrar').addEventListener('click', cerrar);
  overlay.querySelector('.lightbox-prev').addEventListener('click', function () { mover(-1); });
  overlay.querySelector('.lightbox-next').addEventListener('click', function () { mover(1); });
  document.addEventListener('keydown', function (e) {
    if (overlay.hidden) return;
    if (e.key === 'Escape') cerrar();
    else if (e.key === 'ArrowLeft') mover(-1);
    else if (e.key === 'ArrowRight') mover(1);
  });
})();

/* Entrada escalonada de la galería + filtro por categoría.
   Mismo criterio de reveal que el landing (sin IntersectionObserver) y una red
   de seguridad a los 3 s para que ninguna foto se quede escondida. */
(function () {
  var grid = document.querySelector('.galeria-grid');
  if (!grid) return;
  var cards = Array.prototype.slice.call(grid.querySelectorAll('.galeria-card'));
  if (!cards.length) return;
  var chips = Array.prototype.slice.call(document.querySelectorAll('.galeria-chip'));
  var conteo = document.getElementById('galeriaConteo');
  var vacio = document.querySelector('.galeria-vacio');
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  cards.forEach(function (t, i) {
    t.style.setProperty('--galeria-delay', (i * 70) + 'ms');
  });

  if (reduce) {
    cards.forEach(function (t) { t.classList.add('is-in'); });
  } else {
    var pendientes = cards.slice();
    var agendado = false;

    function revisar() {
      var vh = window.innerHeight;
      var quedan = [];
      pendientes.forEach(function (t) {
        if (t.classList.contains('is-hidden')) return;
        var r = t.getBoundingClientRect();
        /* Entra al asomar por el borde inferior, con un 12% de margen */
        if (r.top < vh * 0.88 && r.bottom > 0) t.classList.add('is-in');
        else quedan.push(t);
      });
      pendientes = quedan;
      if (!pendientes.length) {
        window.removeEventListener('scroll', alScroll);
        window.removeEventListener('resize', alScroll);
      }
    }

    function alScroll() {
      if (agendado) return;
      agendado = true;
      requestAnimationFrame(function () { agendado = false; revisar(); });
    }

    window.addEventListener('scroll', alScroll, { passive: true });
    window.addEventListener('resize', alScroll);
    revisar();

    /* Red de seguridad: si algo no se revela (una pestaña que el navegador no
       pinta, un rAF que no llega), a los 3 s se muestra todo igual. Una galería
       escondida es peor que una galería sin animación. */
    setTimeout(function () {
      if (!pendientes.length) return;
      pendientes.forEach(function (t) { t.classList.add('is-in'); });
      pendientes = [];
    }, 3000);
  }

  /* Filtro: al cambiar de chip se ocultan las que no caben y las visibles
     vuelven a entrar con un fundido escalonado (re-deal de la retícula). */
  function aplicarFiltro(filtro) {
    var visibles = 0;
    cards.forEach(function (t) {
      var visible = filtro === 'todo' || t.dataset.cat === filtro;
      t.classList.toggle('is-hidden', !visible);
      if (visible) visibles++;
    });
    if (conteo) conteo.textContent = visibles;
    if (vacio) vacio.hidden = visibles !== 0;

    if (reduce) {
      cards.forEach(function (t) { if (!t.classList.contains('is-hidden')) t.classList.add('is-in'); });
      return;
    }

    cards.forEach(function (t) { t.classList.remove('is-in'); });
    void grid.offsetWidth; /* reinicia la transición antes de reentrar */
    var k = 0;
    cards.forEach(function (t) {
      if (t.classList.contains('is-hidden')) return;
      t.style.setProperty('--galeria-delay', (k * 60) + 'ms');
      t.classList.add('is-in');
      k++;
    });
  }

  chips.forEach(function (chip) {
    chip.addEventListener('click', function () {
      chips.forEach(function (c) {
        c.classList.remove('is-active');
        c.setAttribute('aria-pressed', 'false');
      });
      chip.classList.add('is-active');
      chip.setAttribute('aria-pressed', 'true');
      aplicarFiltro(chip.dataset.filtro);
    });
  });
})();
</script>

<!-- Capa de movimiento del landing (reveal + parallax) ya versionada como el
     resto de assets, para no editar la animación dentro del partial. -->
<script src="assets/js/landing.js?v=<?= LINK_CSS_VERSION ?>" defer></script>
<!-- Capa premium: titulares partidos en lineas, cifras que cuentan y barra de
     progreso. Se carga despues de landing.js para que el reveal general ya este
     montado cuando estas lineas marquen `is-in`. -->
<script src="assets/js/paginas.js?v=<?= LINK_CSS_VERSION ?>" defer></script>