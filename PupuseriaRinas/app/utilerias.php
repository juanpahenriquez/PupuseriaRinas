<?php
/** Helpers pequeños de formato y tiempo para el admin. */

/** "07:00:00" -> "7AM" | "17:30:00" -> "5:30PM" */
function fmt_hora(?string $t): string
{
    if ($t === null || $t === '') {
        return '';
    }
    [$h, $m] = array_map('intval', explode(':', explode(' ', $t)[0]));
    $suffix = $h >= 12 ? 'PM' : 'AM';
    $h12 = $h % 12;
    if ($h12 === 0) {
        $h12 = 12;
    }
    return $h12 . ($m > 0 ? ':' . str_pad((string) $m, 2, '0') : '') . $suffix;
}

/** Fila de horario -> "7AM–11AM · 5PM–9PM" o "Cerrado" o '' */
function rango_dia(?array $h): string
{
    if (!$h) {
        return '';
    }
    if (!empty($h['cerrado'])) {
        return 'Cerrado';
    }
    $partes = [];
    if (!empty($h['am_apertura']) && !empty($h['am_cierre'])) {
        $partes[] = fmt_hora($h['am_apertura']) . '–' . fmt_hora($h['am_cierre']);
    }
    if (!empty($h['pm_apertura']) && !empty($h['pm_cierre'])) {
        $partes[] = fmt_hora($h['pm_apertura']) . '–' . fmt_hora($h['pm_cierre']);
    }
    return implode(' · ', $partes);
}

/** "2026-09-23 10:04:00" -> "Hace 4 min" / "Hace 2 h" / "Ayer" / fecha */
function hace(?string $fecha): string
{
    if (!$fecha) {
        return '';
    }
    $ts = strtotime($fecha);
    $dif = time() - $ts;
    if ($dif < 60) {
        return 'Hace un momento';
    }
    if ($dif < 3600) {
        return 'Hace ' . intdiv($dif, 60) . ' min';
    }
    if ($dif < 86400) {
        return 'Hace ' . intdiv($dif, 3600) . ' h';
    }
    if ($dif < 172800) {
        return 'Ayer';
    }
    return date('d/m', $ts);
}

/** ¿Está abierto ahora según la fila de horario de hoy? */
function esta_abierto(?array $h): bool
{
    if (!$h || !empty($h['cerrado'])) {
        return false;
    }
    $ahora = date('H:i:s');
    foreach ([['am_apertura', 'am_cierre'], ['pm_apertura', 'pm_cierre']] as [$a, $c]) {
        if (!empty($h[$a]) && !empty($h[$c]) && $ahora >= $h[$a] && $ahora <= $h[$c]) {
            return true;
        }
    }
    return false;
}

/** Normaliza la paginación: página válida, total de páginas, rango mostrado y offset SQL. */
function paginar(int $total, int $porPagina = 10, int $pagina = 1): array
{
    $porPagina = max(1, $porPagina);
    $total     = max(0, $total);
    $paginas   = max(1, (int) ceil($total / $porPagina));
    $pagina    = min(max(1, $pagina), $paginas);
    $offset    = ($pagina - 1) * $porPagina;

    return [
        'total'     => $total,
        'pagina'    => $pagina,
        'paginas'   => $paginas,
        'porPagina' => $porPagina,
        'offset'    => $offset,
        'desde'     => $total > 0 ? $offset + 1 : 0,
        'hasta'     => min($offset + $porPagina, $total),
    ];
}

/** Texto buscado por GET (q): espacios normalizados y longitud acotada. */
function q_get(int $max = 60): string
{
    $q = (string) ($_GET['q'] ?? '');
    $q = trim((string) preg_replace('/\s+/u', ' ', $q));
    return mb_substr($q, 0, $max);
}

/** Página pedida por GET (p) — nunca menor a 1. */
function pagina_get(): int
{
    return max(1, (int) ($_GET['p'] ?? 1));
}

/** Valor para un LIKE: escapa los comodines y añade los % de búsqueda parcial. */
function like_param(string $q): string
{
    return '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
}

/** URL de retorno segura: solo .php de la misma carpeta del admin (evita redirecciones externas). */
function url_retorno(?string $valor, string $defecto = 'index.php'): string
{
    $valor = (string) $valor;
    return preg_match('#^[a-z]+\.php(\?[A-Za-z0-9%=&_.+-]*)?$#', $valor) ? $valor : $defecto;
}

/** URL actual del listado: conserva filtros y página tras un POST o una acción GET. */
function url_actual(string $base = ''): string
{
    $base = $base !== '' ? $base : basename((string) ($_SERVER['PHP_SELF'] ?? ''));
    $qs   = (string) ($_SERVER['QUERY_STRING'] ?? '');
    return $base . ($qs !== '' ? '?' . $qs : '');
}
