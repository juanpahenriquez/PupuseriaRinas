<?php
date_default_timezone_set("America/El_Salvador");

// Datos por defecto — se mantienen si la BD no responde o está vacía
// TODO: datos ficticios — reemplazar por la direccion y telefono reales
$direccion_mostrar = "Paseo General Escalón #123, San Salvador";
$mapa_q = "Paseo General Escalón, San Salvador, El Salvador";
$telefono_mostrar = "7000-0000";
$telefono_link = "50370000000"; // codigo pais + numero, solo digitos (ficticio)

// Overrides desde el panel admin (Configuración y Horarios) — fail-silent:
// si MySQL está apagado o no hay datos guardados, quedan los valores de arriba.
require_once __DIR__ . '/../../app/inicio.php';
$pdoPub = db(false);
if ($pdoPub) {
    try {
        $cfgPub = [];
        foreach ($pdoPub->query("SELECT clave, valor FROM configuracion") as $r) {
            $cfgPub[$r['clave']] = (string) $r['valor'];
        }
        if (!empty($cfgPub['direccion'])) $direccion_mostrar = $cfgPub['direccion'];
        if (!empty($cfgPub['maps_query'])) $mapa_q = $cfgPub['maps_query'];
        elseif (!empty($cfgPub['direccion'])) $mapa_q = $cfgPub['direccion'];
        if (!empty($cfgPub['telefono'])) $telefono_mostrar = $cfgPub['telefono'];
        if (!empty($cfgPub['whatsapp'])) $telefono_link = $cfgPub['whatsapp'];

        $nombresDias = [1 => "Lunes", 2 => "Martes", 3 => "Miércoles", 4 => "Jueves", 5 => "Viernes", 6 => "Sábado", 7 => "Domingo"];
        $diasDb = [];
        $hayHorariosDb = false;
        foreach ($pdoPub->query("SELECT * FROM horarios ORDER BY dia") as $h) {
            $d = (int) $h['dia'];
            if (!empty($h['cerrado'])) {
                $diasDb[$d] = [$nombresDias[$d], [null, null]];
                $hayHorariosDb = true;
                continue;
            }
            $am = (!empty($h['am_apertura']) && !empty($h['am_cierre']))
                ? fmt_hora($h['am_apertura']) . '-' . fmt_hora($h['am_cierre']) : null;
            $pm = (!empty($h['pm_apertura']) && !empty($h['pm_cierre']))
                ? fmt_hora($h['pm_apertura']) . '-' . fmt_hora($h['pm_cierre']) : null;
            if ($am || $pm) $hayHorariosDb = true;
            $diasDb[$d] = [$nombresDias[$d], [$am, $pm]];
        }
        if ($hayHorariosDb) {
            foreach ($nombresDias as $d => $nom) {
                if (!isset($diasDb[$d])) $diasDb[$d] = [$nom, [null, null]];
            }
            ksort($diasDb);
            $horariosDesdeAdmin = $diasDb;
        }
    } catch (Throwable $e) {
        // silencio: se conservan los datos por defecto
    }
}

$hoy = (int) date("N"); // 1 = lunes ... 7 = domingo
// Pares [mañana, tarde] en formato 12h; null = cerrado ese turno
$dias = $horariosDesdeAdmin ?? [
    1 => ["Lunes", ["7AM-11AM", "5PM-9PM"]],
    2 => ["Martes", ["7AM-11AM", null]],
    3 => ["Miércoles", ["7AM-11AM", "5PM-9PM"]],
    4 => ["Jueves", ["7AM-11AM", "5PM-9PM"]],
    5 => ["Viernes", ["7AM-11AM", "5PM-9PM"]],
    6 => ["Sábado", ["7AM-11AM", "5PM-9PM"]],
    7 => ["Domingo", [null, "5PM-9PM"]],
];

// Resumen de HOY para el indicador de estado (abierto / cerrado)
$turnosHoy = $dias[$hoy][1] ?? [null, null];
$hoyPartes = [];
foreach ($turnosHoy as $t) { if (!empty($t)) $hoyPartes[] = $t; }
$hoyStr = $hoyPartes ? implode(' · ', $hoyPartes) : 'Cerrado';
$hoyAbierto = !empty($hoyPartes);

// Enlace de WhatsApp con el mensaje ya escrito: el boton de la ficha y el CTA
// abren el chat con la consulta lista, no en blanco.
$waEnlace = 'https://wa.me/' . $telefono_link . '?text=' . rawurlencode('Hola Rinas, quiero hacer un pedido.');
$mapsEnlace = 'https://www.google.com/maps/search/?api=1&query=' . urlencode($mapa_q);
?>

<!-- ============================================================================
     Ubicación · mismo lenguaje que el landing (bloque LANDING de estilos.css):
     hero a sangre con parallax -> mapa + ficha de datos -> horarios -> galería
     del lugar con parallax -> cinta -> CTA.
     El movimiento lo pone assets/js/landing.js con data-parallax / data-reveal.
     ========================================================================== -->

<!-- Barra de progreso de lectura (paginas.js) -->
<div class="px-progress" aria-hidden="true"><span class="px-progress-fill"></span></div>

<!-- ------------------------------------------------------------ 01 · hero -->
<section class="px-hero" id="inicio">
    <span class="px-hero-media" aria-hidden="true">
        <img src="assets/img/galeria/lugar-262047.jpg?v=<?= LINK_CSS_VERSION ?>" alt="" data-parallax="9" fetchpriority="high" decoding="async">
        <span class="px-hero-veil"></span>
    </span>
    <span class="px-grain" aria-hidden="true"></span>

    <div class="px-hero-content">
        <div class="container">
            <span class="nl-eyebrow nl-eyebrow--claro" data-reveal>Dónde estamos</span>

            <h1 class="px-hero-title" data-split>Ven a la<br>esquina de <span class="px-hero-accent">Rinas</span></h1>

            <p class="px-hero-lead" data-reveal data-reveal-delay="520">
                <?= htmlspecialchars($direccion_mostrar) ?>. Café caliente, pupusas al
                momento y una mesa esperándote.
            </p>

            <div class="px-hero-cta" data-reveal data-reveal-delay="640">
                <a class="nl-btn nl-btn--naranja" href="<?= htmlspecialchars($mapsEnlace) ?>" target="_blank" rel="noopener">Cómo llegar</a>
                <a class="nl-btn nl-btn--ghost" href="#horarios">Ver horarios</a>
            </div>

            <!-- Riel de datos: los tres minimos que se busca al llegar. -->
            <div class="px-hero-rail" data-reveal data-reveal-delay="760">
                <div class="px-hero-cell">
                    <span class="px-hero-cell-label">Estado hoy</span>
                    <span class="px-hero-cell-value"><?php echo $hoyAbierto ? 'Abierto' : 'Cerrado'; ?> · <?php echo htmlspecialchars($hoyStr); ?></span>
                </div>
                <div class="px-hero-cell">
                    <span class="px-hero-cell-label">Teléfono</span>
                    <span class="px-hero-cell-value"><?= htmlspecialchars($telefono_mostrar) ?></span>
                </div>
                <div class="px-hero-cell">
                    <span class="px-hero-cell-label">Encargos</span>
                    <span class="px-hero-cell-value">Tienda · Para llevar</span>
                </div>
            </div>
        </div>
    </div>

    <a class="px-hero-scroll" href="#mapa" aria-label="Ir al mapa">
        <span class="px-hero-scroll-line" aria-hidden="true"></span>
    </a>
</section>

<!-- ------------------------------------------------- 02 · mapa + ficha ---- -->
<section class="px-section px-section--blanco" id="mapa" aria-labelledby="ubMapaTitulo">
    <div class="container">
        <div class="px-head" data-reveal>
            <span class="px-head-num">01 — Mapa</span>
            <span class="px-head-line" aria-hidden="true"></span>
        </div>

        <div class="px-ub-head">
            <div>
                <span class="nl-eyebrow" data-reveal>Punto en el mapa</span>
                <h2 class="nl-display" id="ubMapaTitulo" data-split data-reveal data-reveal-delay="60">Ubicación</h2>
                <p class="px-ub-head-dir">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    <span><?= htmlspecialchars($direccion_mostrar) ?></span>
                </p>
            </div>
            <span class="px-estado<?php echo $hoyAbierto ? '' : ' px-estado-cerrado'; ?>" data-reveal data-reveal-delay="120">
                <span class="px-estado-pulso" aria-hidden="true"></span>
                <?php echo $hoyAbierto ? 'Abierto hoy' : 'Cerrado hoy'; ?> · <?php echo htmlspecialchars($hoyStr); ?>
            </span>
        </div>

        <!-- El mapa manda (1.7fr) y la ficha acompaña: dirección, teléfono y
             los dos botones de acción, sobre azul. -->
        <div class="px-ub-layout" data-reveal data-reveal-delay="60">
            <div class="px-ub-map">
                <iframe title="Mapa Pupusería Rinas" src="https://www.google.com/maps?q=<?php echo urlencode($mapa_q); ?>&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
            </div>

            <div class="px-ub-panel">
                <div>
                    <span class="px-ub-block-label">Dirección</span>
                    <p class="px-ub-block-text"><?= htmlspecialchars($direccion_mostrar) ?></p>
                </div>
                <div>
                    <span class="px-ub-block-label">Teléfono</span>
                    <p class="px-ub-block-text"><?= htmlspecialchars($telefono_mostrar) ?></p>
                    <span class="px-ub-block-sub">Encargos para llevar y encargos de la casa.</span>
                </div>
                <div>
                    <span class="px-ub-block-label">Horario de hoy</span>
                    <p class="px-ub-block-text"><?= $hoyAbierto ? htmlspecialchars($hoyStr) : 'Hoy cerrado' ?></p>
                    <span class="px-ub-block-sub">Los horarios de toda la semana están más abajo.</span>
                </div>
                <div class="px-ub-acciones">
                    <a class="nl-btn nl-btn--naranja" href="<?= htmlspecialchars($mapsEnlace) ?>" target="_blank" rel="noopener">Google Maps</a>
                    <a class="nl-btn nl-btn--ghost" href="<?= htmlspecialchars($waEnlace) ?>" target="_blank" rel="noopener">WhatsApp</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ------------------------------------------------------- 03 · horarios --- -->
<section class="px-section px-section--crema" id="horarios" aria-labelledby="ubHorariosTitulo">
    <div class="container">
        <div class="px-head" data-reveal>
            <span class="px-head-num">02 — Horarios</span>
            <span class="px-head-line" aria-hidden="true"></span>
        </div>

        <div class="px-grid-2">
            <div>
                <span class="nl-eyebrow" data-reveal>Horario</span>
                <h2 class="nl-display" id="ubHorariosTitulo" data-split data-reveal data-reveal-delay="60">
                    Cuando<br>abrimos
                </h2>
                <p class="nl-lead" data-reveal data-reveal-delay="120">
                    Servimos desayuno hasta media mañana y de nuevo por la tarde. El
                    comal se enciende temprano, pero el café nunca falla.
                </p>
                <div class="d-flex gap-3 flex-wrap mt-4" data-reveal data-reveal-delay="180">
                    <a class="nl-btn nl-btn--naranja" href="<?= htmlspecialchars($waEnlace) ?>" target="_blank" rel="noopener">Pedir por WhatsApp</a>
                </div>
            </div>

            <!-- Filas tipo índice: el día de hoy se marca en naranja. -->
            <div class="px-horarios" data-reveal data-reveal-delay="60">
                <?php foreach ($dias as $n => [$nombre, $turnos]): ?>
                <div class="px-horario<?php echo $n === $hoy ? ' es-hoy' : ''; ?>">
                    <span class="px-horario-dia">
                        <span class="px-horario-punto" aria-hidden="true"></span>
                        <?= htmlspecialchars($nombre) ?>
                        <?php if ($n === $hoy): ?>
                        <span class="px-horario-tag">Hoy</span>
                        <?php endif; ?>
                    </span>
                    <span class="px-horario-horas">
                        <?php
                            $partes = [];
                            if (!empty($turnos[0])) $partes[] = '<span>' . htmlspecialchars($turnos[0]) . '</span>';
                            if (!empty($turnos[1])) $partes[] = '<span>' . htmlspecialchars($turnos[1]) . '</span>';
                            echo $partes ? implode('', $partes) : '<span class="cerrado">Cerrado</span>';
                        ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ------------------------------------------ 04 · galería del lugar ----- -->
<section class="px-section px-section--blanco" aria-labelledby="ubGaleriaTitulo">
    <div class="container">
        <div class="px-head" data-reveal>
            <span class="px-head-num">03 — El lugar</span>
            <span class="px-head-line" aria-hidden="true"></span>
        </div>

        <div class="px-ub-head">
            <div>
                <span class="nl-eyebrow" data-reveal>Nuestro lugar</span>
                <h2 class="nl-display" id="ubGaleriaTitulo" data-split data-reveal data-reveal-delay="60">Así se ve Rinas</h2>
            </div>
            <span class="px-strip-num" aria-hidden="true">La sala, la mesa y la compañía</span>
        </div>

        <!-- Retícula a sangre: cada ventana recorta y su capa interior es la que
             se desplaza con el scroll, a velocidad alterna. El número aparece
             al pasar el raton, como en un catálogo. -->
        <div class="px-shots">
            <figure class="px-shot">
                <span class="px-shot-num" aria-hidden="true">01</span>
                <span class="px-shot-media" data-parallax="9" aria-hidden="true">
                    <img src="assets/img/galeria/pexels-cafe-interior.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Interior de Pupusería Rinas" loading="lazy" decoding="async">
                </span>
                <span class="px-shot-veil" aria-hidden="true"></span>
                <figcaption>La sala, con luz natural</figcaption>
            </figure>
            <figure class="px-shot">
                <span class="px-shot-num" aria-hidden="true">02</span>
                <span class="px-shot-media" data-parallax="12" aria-hidden="true">
                    <img src="assets/img/galeria/lugar-262047.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Zona de mesas de Pupusería Rinas" loading="lazy" decoding="async">
                </span>
                <span class="px-shot-veil" aria-hidden="true"></span>
                <figcaption>Un rincón para el café</figcaption>
            </figure>
            <figure class="px-shot">
                <span class="px-shot-num" aria-hidden="true">03</span>
                <span class="px-shot-media" data-parallax="8" aria-hidden="true">
                    <img src="assets/img/galeria/pexels-cafe-sala.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Sala de Pupusería Rinas" loading="lazy" decoding="async">
                </span>
                <span class="px-shot-veil" aria-hidden="true"></span>
                <figcaption>Para comer aquí o llevar</figcaption>
            </figure>
            <figure class="px-shot">
                <span class="px-shot-num" aria-hidden="true">04</span>
                <span class="px-shot-media" data-parallax="13" aria-hidden="true">
                    <img src="assets/img/galeria/lugar-941861.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Ambiente de Pupusería Rinas" loading="lazy" decoding="async">
                </span>
                <span class="px-shot-veil" aria-hidden="true"></span>
                <figcaption>Ambiente cálido</figcaption>
            </figure>
            <figure class="px-shot">
                <span class="px-shot-num" aria-hidden="true">05</span>
                <span class="px-shot-media" data-parallax="9" aria-hidden="true">
                    <img src="assets/img/galeria/pexels-cafe-cliente.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Clientes disfrutando en Pupusería Rinas" loading="lazy" decoding="async">
                </span>
                <span class="px-shot-veil" aria-hidden="true"></span>
                <figcaption>Buena compañía</figcaption>
            </figure>
            <figure class="px-shot">
                <span class="px-shot-num" aria-hidden="true">06</span>
                <span class="px-shot-media" data-parallax="11" aria-hidden="true">
                    <img src="assets/img/galeria/lugar-3184192.jpg?v=<?= LINK_CSS_VERSION ?>" alt="Gente disfrutando en Pupusería Rinas" loading="lazy" decoding="async">
                </span>
                <span class="px-shot-veil" aria-hidden="true"></span>
                <figcaption>Te esperamos</figcaption>
            </figure>
        </div>
    </div>
</section>

<!-- ------------------------------------------- cinta de palabras (común) -->
<div class="nl-marquee" aria-hidden="true">
    <div class="nl-marquee-track">
        <span>Ven con hambre</span><span>&#183;</span><span>Sal con café</span><span>&#183;</span>
        <span>Comal caliente</span><span>&#183;</span><span>Casa Rinas</span><span>&#183;</span>
        <span>Pupusas al momento</span><span>&#183;</span><span>Te esperamos</span><span>&#183;</span>
        <span>Ven con hambre</span><span>&#183;</span><span>Sal con café</span><span>&#183;</span>
        <span>Comal caliente</span><span>&#183;</span><span>Casa Rinas</span><span>&#183;</span>
        <span>Pupusas al momento</span><span>&#183;</span><span>Te esperamos</span><span>&#183;</span>
    </div>
</div>

<!-- -------------------------------------------------------------- 05 · CTA -->
<section class="px-cta">
    <div class="container">
        <div class="px-cta-card">
            <span class="px-cta-brillo" aria-hidden="true"></span>
            <span class="px-cta-vineta" aria-hidden="true"></span>
            <span class="px-grain px-grain--fuerte" aria-hidden="true"></span>
            <span class="nl-eyebrow" data-reveal>¿Se te antojó?</span>
            <h2 class="px-cta-title" data-split data-reveal data-reveal-delay="60">Café caliente y pupusas al momento</h2>
            <p class="px-cta-text" data-reveal data-reveal-delay="240">
                Mira lo que tenemos hoy o escríbenos y encargamos tu pedido. ¡Te esperamos
                con el comal caliente!
            </p>
            <div class="px-cta-acciones" data-reveal data-reveal-delay="320">
                <a class="nl-btn nl-btn--naranja" href="menu.php">Ver el menú</a>
                <a class="nl-btn nl-btn--ghost" href="<?= htmlspecialchars($waEnlace) ?>" target="_blank" rel="noopener">Pedir por WhatsApp</a>
            </div>
        </div>
    </div>
</section>

<!-- Capa de movimiento del landing (reveal + parallax), versionada igual que
     el resto de assets para no duplicar la animación dentro del partial. -->
<script src="assets/js/landing.js?v=<?= LINK_CSS_VERSION ?>" defer></script>
<!-- Capa premium: titulares partidos en lineas y barra de progreso. -->
<script src="assets/js/paginas.js?v=<?= LINK_CSS_VERSION ?>" defer></script>

<!-- Capa de movimiento del landing (reveal + parallax), versionada igual que
     el resto de assets para no duplicar la animación dentro del partial. -->
<script src="assets/js/landing.js?v=<?= LINK_CSS_VERSION ?>" defer></script>