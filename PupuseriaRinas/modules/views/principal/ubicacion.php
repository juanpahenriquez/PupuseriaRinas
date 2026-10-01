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
require_once __DIR__ . '/../../../config/conexion.php';
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

// Página: horarios + mapa + franja de accesos
?>

<section class="page-hero">
    <img class="page-hero-bg" src="assets/img/pexels-allanglezg-30005123.jpg" alt="Pupusería Rinas">
    <div class="container position-relative">
        <h1 class="display-5 fw-bold text-white mb-2">Ubicación</h1>
        <p class="lead text-white-50 mb-0"><?php echo $direccion_mostrar; ?></p>
    </div>
</section>

<section class="horarios-rinas py-4 py-md-5">
    <div class="container">
        <div class="row g-0">
            <div class="col-12">
                <div class="horario-card horario-full">
                    <div class="semana-grid">
                    <?php foreach ($dias as $n => [$nombre, $turnos]): ?>
                    <div class="semana-dia<?php echo $n === $hoy ? " es-hoy" : ""; ?>">
                        <span class="semana-nombre">
                            <?php echo $nombre; ?>
                            <?php if ($n === $hoy): ?><span class="hoy-badge">Hoy</span><?php endif; ?>
                        </span>
                        <?php foreach ($turnos as $turno): ?>
                            <?php if ($turno): ?>
                        <span class="semana-hora"><?php echo $turno; ?></span>
                            <?php else: ?>
                        <span class="semana-hora cerrado">Cerrado</span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="mapa-card">
                <div class="mapa-rinas">
                    <iframe title="Mapa Pupusería Rinas" src="https://www.google.com/maps?q=<?php echo urlencode($mapa_q); ?>&output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
                </div>
                <div class="dir-bloque dir-h">
                    <div class="dir-h-info">
                    <p class="dir-eyebrow">Encuéntranos en</p>
                    <p class="dir-address">
                        <span class="dir-pin"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
                        <span><?php echo $direccion_mostrar; ?></span>
                    </p>
                    </div>
                    <div class="dir-h-actions">
                        <a class="btn rounded-pill btn-rinas-naranja" target="_blank" rel="noopener" href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($mapa_q); ?>">Cómo llegar</a>
                        <a class="btn rounded-pill btn-rinas-blanco" target="_blank" rel="noopener" href="https://wa.me/<?php echo $telefono_link; ?>">WhatsApp</a>
                        <a class="btn rounded-pill btn-rinas-blanco" href="tel:+<?php echo $telefono_link; ?>"><?php echo $telefono_mostrar; ?></a>
                    </div>
                </div>
                </div>
            </div>
        </div>
    </div>
</section>
