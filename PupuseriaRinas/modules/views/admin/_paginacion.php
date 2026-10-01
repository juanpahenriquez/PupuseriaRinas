<?php
/**
 * Parcial: barra de paginación. Mismo contrato de $paginacion que _buscador.php.
 * Los enlaces conservan los filtros actuales (params) y la búsqueda (q).
 */
$paginacion = $paginacion ?? [
    'base' => '', 'q' => '', 'params' => [], 'total' => 0,
    'porPagina' => 10, 'pagina' => 1, 'etiqueta' => '', 'placeholder' => 'Buscar…',
];
$pgNav = paginar((int) $paginacion['total'], (int) $paginacion['porPagina'], (int) $paginacion['pagina']);
if ($pgNav['paginas'] > 1):
    $urlPag = function (int $n) use ($paginacion): string {
        $params = array_filter((array) $paginacion['params'], fn($v) => $v !== '' && $v !== null);
        if ((string) $paginacion['q'] !== '') {
            $params['q'] = (string) $paginacion['q'];
        }
        if ($n > 1) {
            $params['p'] = $n;
        }
        return (string) $paginacion['base'] . ($params ? '?' . http_build_query($params) : '');
    };

    // Números visibles: primera, última y las cercanas a la actual
    $numeros = [];
    for ($i = 1; $i <= $pgNav['paginas']; $i++) {
        if ($i === 1 || $i === $pgNav['paginas'] || abs($i - $pgNav['pagina']) <= 2) {
            $numeros[] = $i;
        }
    }
?>
<nav class="admin-paginacion" aria-label="Paginación de <?= htmlspecialchars((string) $paginacion['etiqueta']) ?>">
    <ul class="pagination mb-0 flex-wrap">
        <li class="page-item <?= $pgNav['pagina'] <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= htmlspecialchars($urlPag(max(1, $pgNav['pagina'] - 1))) ?>" <?= $pgNav['pagina'] <= 1 ? 'tabindex="-1" aria-disabled="true"' : '' ?>>‹ Anterior</a>
        </li>
        <?php $anterior = 0; foreach ($numeros as $n): ?>
        <?php if ($anterior > 0 && $n - $anterior > 1): ?>
        <li class="page-item disabled"><span class="page-link">…</span></li>
        <?php endif; ?>
        <li class="page-item <?= $n === $pgNav['pagina'] ? 'active' : '' ?>">
            <a class="page-link" href="<?= htmlspecialchars($urlPag($n)) ?>" <?= $n === $pgNav['pagina'] ? 'aria-current="page"' : '' ?>><?= $n ?></a>
        </li>
        <?php $anterior = $n; endforeach; ?>
        <li class="page-item <?= $pgNav['pagina'] >= $pgNav['paginas'] ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= htmlspecialchars($urlPag(min($pgNav['paginas'], $pgNav['pagina'] + 1))) ?>" <?= $pgNav['pagina'] >= $pgNav['paginas'] ? 'tabindex="-1" aria-disabled="true"' : '' ?>>Siguiente ›</a>
        </li>
    </ul>
</nav>
<?php endif; ?>
