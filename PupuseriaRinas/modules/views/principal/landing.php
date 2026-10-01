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
<section class="hero-rinas hero-video p-4 p-md-5">
    <video class="hero-video-bg" autoplay muted loop playsinline preload="metadata">
        <source src="assets/video/8448183-hd_1920_1080_24fps.mp4" type="video/mp4">
    </video>
    <div class="container position-relative">
        <div class="row align-items-center g-4">
            <div class="col-12 col-md-7">
                <h1 class="display-4 fw-bold mb-3">
                    <span class="d-block text-white">Café caliente</span>
                    <span class="d-block text-rinas-naranja">y Pupusas Rinas</span>
                </h1>

                <p class="lead mb-4 text-white-50">Recoge en tienda o pide para llevar. Sabor casero todos los días.</p>

                <div class="d-flex gap-3 align-items-center mb-4 flex-wrap hero-cta">
                    <a href="https://wa.me/50370000000?" class="btn rounded-pill px-5 py-2 btn-rinas-naranja">HAZ TU PEDIDO!</a>
                </div>

            </div>
        </div>
    </div>
</section>


<section class="enlaces-rinas" aria-labelledby="enlacesTitulo">
        <div class="row g-3 g-lg-4">
            <div class="col-12 col-md-4">
                <a href="menu.php" class="rinas-card">
                    <img class="rinas-card-foto" src="assets/img/MENU LANDING.png"
                         alt="" aria-hidden="true" loading="lazy" decoding="async">
                    <span class="rinas-card-contenido">
                        <span class="rinas-card-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M6 8h15l-1.5 12.5a1 1 0 0 1-1 .5H5.5a1 1 0 0 1-1-.5L3 8h3z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                        </span>
                        <span class="rinas-card-pie">
                            <span class="rinas-card-datos">
                                <span class="rinas-card-titulo">Ver Menú</span>
                                <span class="rinas-card-texto"><?= htmlspecialchars($textoMenu) ?></span>
                            </span>
                            <span class="rinas-card-flecha" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                            </span>
                        </span>
                    </span>
                </a>
            </div>

            <div class="col-12 col-md-4">
                <a href="ubicacion.php" class="rinas-card">
                    <img class="rinas-card-foto" src="assets/img/UBICACION LANDING.png"
                         alt="" aria-hidden="true" loading="lazy" decoding="async">
                    <span class="rinas-card-contenido">
                        <span class="rinas-card-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        </span>
                        <span class="rinas-card-pie">
                            <span class="rinas-card-datos">
                                <span class="rinas-card-titulo">Ubicación</span>
                                <span class="rinas-card-texto"><?= htmlspecialchars($textoUbicacion) ?></span>
                            </span>
                            <span class="rinas-card-flecha" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                            </span>
                        </span>
                    </span>
                </a>
            </div>

            <div class="col-12 col-md-4">
                <a href="nosotros.php" class="rinas-card">
                    <img class="rinas-card-foto" src="assets/img/ACERCA DE NOSOTROS LANDING.png"
                         alt="" aria-hidden="true" loading="lazy" decoding="async">
                    <span class="rinas-card-contenido">
                        <span class="rinas-card-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3.5"/><path d="M2.5 20a6.5 6.5 0 0 1 13 0"/><path d="M16 5a3.5 3.5 0 0 1 0 7"/><path d="M17.5 14.5a6.5 6.5 0 0 1 4 5.5"/></svg>
                        </span>
                        <span class="rinas-card-pie">
                            <span class="rinas-card-datos">
                                <span class="rinas-card-titulo">Nosotros</span>
                                <span class="rinas-card-texto">Desde 2018 haciendo café y pupusas</span>
                            </span>
                            <span class="rinas-card-flecha" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                            </span>
                        </span>
                    </span>
                </a>
            </div>

        </div>
    </div>
</section>

<!-- =========================================================
     Galería: carrusel principal sincronizado con miniaturas (Splide)
     ========================================================= -->
<section class="landing-galeria-rinas" aria-labelledby="galeriaTitulo">
    <div class="container">
        <span class="landing-galeria-eyebrow">Galería</span>
        <h2 class="landing-galeria-title" id="galeriaTitulo">Directo desde nuestro comal</h2>
        <p class="landing-galeria-lead mb-4">Pupusas recién hechas, licuados naturales y el ambiente de la casa.</p>

        <div id="main-slider" class="splide" aria-label="Galería de Pupusería Rinas">
            <div class="splide__track">
                <ul class="splide__list">
                    <li class="splide__slide"><img src="assets/img/galeria/pupusas-comal.jpg" alt="Pupusas recién salidas del comal"></li>
                    <li class="splide__slide"><img src="assets/img/galeria/pupusas-mesa.jpg" alt="El ambiente de la casa" loading="lazy"></li>
                    <li class="splide__slide"><img src="assets/img/pupa_camaron.png" alt="Pupusa de camarón Sabor Mediterráneo" loading="lazy"></li>
                    <li class="splide__slide"><img src="assets/img/pupa_chile.png" alt="Pupusa de chile" loading="lazy"></li>
                    <li class="splide__slide"><img src="assets/img/licuado.png" alt="Licuado clásico con banana" loading="lazy"></li>
                    <li class="splide__slide"><img src="assets/img/galeria_1_600.jpg" alt="Ambiente Pupusería Rinas" loading="lazy"></li>
                </ul>
            </div>
        </div>

        <div id="thumbnail-slider" class="splide" aria-label="Ir a la imagen de la galería">
            <div class="splide__track">
                <ul class="splide__list">
                    <li class="splide__slide"><img src="assets/img/galeria/pupusas-comal.jpg" alt=""></li>
                    <li class="splide__slide"><img src="assets/img/galeria/pupusas-mesa.jpg" alt="" loading="lazy"></li>
                    <li class="splide__slide"><img src="assets/img/pupa_camaron.png" alt="" loading="lazy"></li>
                    <li class="splide__slide"><img src="assets/img/pupa_chile.png" alt="" loading="lazy"></li>
                    <li class="splide__slide"><img src="assets/img/licuado.png" alt="" loading="lazy"></li>
                    <li class="splide__slide"><img src="assets/img/galeria_1_600.jpg" alt="" loading="lazy"></li>
                </ul>
            </div>
        </div>
    </div>
</section>

<script src="<?= LINK_SPLIDE_JS ?>"></script>
<script>
(function(){
  if (!window.Splide) return; // CDN caído: no romper el resto de la página

  var main = new Splide( '#main-slider', {
    type       : 'fade',
    heightRatio: 0.45,
    pagination : false,
    arrows     : false,
    cover      : true,
  } );

  var thumbnails = new Splide( '#thumbnail-slider', {
    rewind          : true,
    fixedWidth      : 104,
    fixedHeight     : 58,
    isNavigation    : true,
    gap             : 10,
    focus           : 'center',
    pagination      : false,
    cover           : true,
    dragMinThreshold: {
      mouse: 4,
      touch: 10,
    },
    breakpoints : {
      640: {
        fixedWidth  : 66,
        fixedHeight : 38,
      },
    },
  } );

  main.sync( thumbnails );
  main.mount();
  thumbnails.mount();
})();
</script>

<section class="destacado-rinas py-4 py-md-5">
    <div class="">
        <div class="row align-items-center g-4 g-lg-5">
            <!-- Visual: circulo + pupusa como en referencia Nike -->
            <div class="col-12 col-lg-6">
                <div class="destacado-visual">
                    <div class="destacado-circulo"></div>
                    <img class="destacado-img" src="assets/img/pupa_camaron.png" alt="Pupusa de camarón Sabor Mediterráneo" loading="lazy">
                </div>
            </div>
            <!-- Detalle: pill + titulo + texto + precio/sizes + botones como Nike -->
            <div class="col-12 col-lg-6">
                <span class="destacado-pill">Sabor Mediterráneo</span>
                <h2 class="destacado-titulo">Pupusa de<br>Camarón</h2>
                <p class="destacado-desc">Camarón jugoso y queso fundido en tortilla gruesa dorada a la plancha. Un giro costeño con alma salvadoreña: marisco fresco, queso cremoso y el sazón de la casa, servida siempre caliente con curtido y salsa.</p>
                <div class="row g-3 mb-3">
                    <div class="col-5 col-sm-4">
                        <div class="destacado-label">Precio:</div>
                        <div class="destacado-price" id="destacadoPrice">$1.50</div>
                    </div>
                    <div class="col-7 col-sm-8">
                        <div class="destacado-label">Unidades:</div>
                        <div class="destacado-sizes" id="destacadoSizes">
                            <button class="size-pill active" type="button" data-qty="1" data-price="1.50">1</button>
                            <button class="size-pill" type="button" data-qty="3" data-price="4.50">3</button>
                            <button class="size-pill" type="button" data-qty="6" data-price="9.00">6</button>
                            <button class="size-pill" type="button" data-qty="12" data-price="18.00">12</button>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 flex-wrap mb-3">
                    <a href="llevar.php" class="btn btn-destacado-principal">Pide para llevar</a>
                    <button class="btn-fav" type="button" aria-label="Favorito"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-4.5-2.8-7-6.5A4.5 4.5 0 0 1 12 7a4.5 4.5 0 0 1 7 7.5c-2.5 3.7-7 6.5-7 6.5z"/></svg></button>
                    <a href="menu.php" class="btn btn-destacado-sec">Ver Menú</a>
                </div>
                <div class="destacado-chips">
                    <span class="chip-rinas"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg> 15 min</span>                </div>
            </div>
        </div>
    </div>
</section>


<section class="licuado-rinas" aria-labelledby="licuadoTitulo">
    <div class="">
        <div class="licuado-stages">
            <div class="row g-0 align-items-stretch">
                <div class="col-12 col-lg-6">
                    <div class="licuado-copy">
                        <span class="licuado-eyebrow">BEBIDAS DE LA CASA</span>
                        <h2 class="licuado-title" id="licuadoTitulo">Licuados</h2>
                        <p class="licuado-lead">Una forma deliciosa de acompañar tus pupusas.</p>
                        <div class="licuado-ingredients" aria-label="Ingredientes destacados">
                            <span>FRUTAS</span>
                            <span>LECHE</span>
                            <span>NATURALES</span>
                        </div>
                        <a class="btn licuado-cta" href="llevar.php">Pide para llevar <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg></a>
                    </div>
                </div>
                <div class="col-12 col-lg-6">
                    <div class="licuado-visual">
                        <span class="licuado-line" aria-hidden="true"></span>
                        <div id="licuadoCarousel" class="carousel slide licuado-carousel" data-bs-ride="carousel" data-bs-interval="4200" data-bs-touch="true" aria-label="Tipos de licuado">
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
                            <div class="licuado-carousel-controls">
                                <button class="licuado-carousel-control" type="button" data-bs-target="#licuadoCarousel" data-bs-slide="prev" aria-label="Ver licuado anterior">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                                </button>
                                <button class="licuado-carousel-control" type="button" data-bs-target="#licuadoCarousel" data-bs-slide="next" aria-label="Ver licuado siguiente">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                                </button>
                            </div>
                            <div class="carousel-indicators licuado-carousel-indicators">
                                <button class="active" type="button" data-bs-target="#licuadoCarousel" data-bs-slide-to="0" aria-label="Ver licuado clásico" aria-current="true"></button>
                                <button type="button" data-bs-target="#licuadoCarousel" data-bs-slide-to="1" aria-label="Ver licuado de fresa"></button>
                                <button type="button" data-bs-target="#licuadoCarousel" data-bs-slide-to="2" aria-label="Ver licuado de Oreo"></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>



<section class="trabaja-rinas" aria-labelledby="trabajaTitulo">
    <div class="">
        <div class="trabaja-panel">
            <div class="row g-0">
                <div class="col-12 col-lg-5">
                    <div class="trabaja-intro">
                        <div class="trabaja-intro-content">
                            <span class="trabaja-eyebrow">ÚNETE AL EQUIPO</span>
                            <h2 class="trabaja-title" id="trabajaTitulo">Trabaja con nosotros</h2>
                            <p class="trabaja-sub mb-0">¿Te apasiona cocinar, servir o llevar nuestro sabor? Encuentra tu lugar en el equipo Rinas.</p>
                            <div class="trabaja-areas" aria-label="Áreas del equipo">
                                <span>Cocina</span>
                                <span>Servicio</span>
                                <span>Reparto</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-lg-7">
                    <div class="trabaja-list">
                        <div class="trabaja-list-header">
                            <span class="trabaja-list-eyebrow">ÁREAS DEL EQUIPO</span>
                            <h3>Encuentra tu lugar</h3>
                        </div>
                        <div class="trabaja-roles">
                            <a class="trabaja-role-card" target="_blank" rel="noopener" href="https://wa.me/50370000000?text=Hola%20quiero%20aplicar%20para%20Cocina%20en%20Rinas" aria-label="Postular a Cocina por WhatsApp">
                                <span class="trabaja-role-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 13a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2H6z"/><path d="M8 15v4"/><path d="M12 15v4"/><path d="M16 15v4"/><path d="M12 5v4"/></svg>
                                </span>
                                <span class="trabaja-role-copy">
                                    <span class="trabaja-role-name">Cocina</span>
                                    <span class="trabaja-role-desc">Prepara pupusas con sazón casero y forma parte del corazón de Rinas.</span>
                                </span>
                                <span class="trabaja-role-arrow" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                                </span>
                            </a>
                            <a class="trabaja-role-card" target="_blank" rel="noopener" href="https://wa.me/50370000000?text=Hola%20quiero%20aplicar%20para%20Servicio%20en%20Rinas" aria-label="Postular a Servicio por WhatsApp">
                                <span class="trabaja-role-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.2"/><path d="M6 18a6 6 0 0 1 12 0"/><path d="M18 8a4 4 0 0 1 4 4v2h-4"/></svg>
                                </span>
                                <span class="trabaja-role-copy">
                                    <span class="trabaja-role-name">Servicio</span>
                                    <span class="trabaja-role-desc">Ofrece atención cercana y ágil para que cada visita se sienta como en casa.</span>
                                </span>
                                <span class="trabaja-role-arrow" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                                </span>
                            </a>
                            <a class="trabaja-role-card" target="_blank" rel="noopener" href="https://wa.me/50370000000?text=Hola%20quiero%20aplicar%20para%20Reparto%20en%20Rinas" aria-label="Postular a Reparto por WhatsApp">
                                <span class="trabaja-role-icon" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 17a2 2 0 1 0 4 0a2 2 0 0 0-4 0z"/><path d="M15 17a2 2 0 1 0 4 0a2 2 0 0 0-4 0z"/><path d="M7 17h10l2-6H6z"/><path d="M6 11V9h4"/><circle cx="7" cy="17" r="0.5" fill="currentColor"/><circle cx="17" cy="17" r="0.5" fill="currentColor"/></svg>
                                </span>
                                <span class="trabaja-role-copy">
                                    <span class="trabaja-role-name">Reparto</span>
                                    <span class="trabaja-role-desc">Lleva el sabor Rinas a domicilio con puntualidad y conocimiento de la ruta.</span>
                                </span>
                                <span class="trabaja-role-arrow" aria-hidden="true">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script src="assets/js/carousel.js"></script>
<script>
(function(){
  var wrap=document.getElementById('destacadoSizes');
  var priceEl=document.getElementById('destacadoPrice');
  if(!wrap||!priceEl) return;
  var favBtn=document.querySelector('.btn-fav');
  if(favBtn){
    favBtn.addEventListener('click',function(){
      favBtn.classList.toggle('is-fav');
      var s=favBtn.querySelector('svg');
      if(s) s.style.fill=favBtn.classList.contains('is-fav') ? 'currentColor' : 'none';
    });
  }
  wrap.addEventListener('click',function(e){
    var btn=e.target.closest('.size-pill');
    if(!btn) return;
    wrap.querySelectorAll('.size-pill').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    var p=btn.getAttribute('data-price');
    priceEl.textContent='$'+p;
    // feedback sutil en imagen
    var img=document.querySelector('.destacado-img');
    if(img){ img.style.transform='rotate(-8deg) scale(0.97)'; setTimeout(function(){ img.style.transform=''; },180); }
  });
})();
</script>
