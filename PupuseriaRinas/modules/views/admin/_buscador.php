<?php
/**
 * Parcial: buscador + contador de resultados de un listado del admin.
 * Requiere $paginacion = [
 *   'base' => 'usuarios.php', 'q' => '', 'params' => [...], 'total' => 0,
 *   'porPagina' => 10, 'pagina' => 1, 'etiqueta' => 'usuarios', 'placeholder' => '...'
 * ];
 */
$paginacion = $paginacion ?? [
    'base' => '', 'q' => '', 'params' => [], 'total' => 0,
    'porPagina' => 10, 'pagina' => 1, 'etiqueta' => '', 'placeholder' => 'Buscar…',
];
$pgBusca = paginar((int) $paginacion['total'], (int) $paginacion['porPagina'], (int) $paginacion['pagina']);
$qActual = (string) $paginacion['q'];
$filtros = array_filter((array) $paginacion['params'], fn($v) => $v !== '' && $v !== null);
$sinBusqueda = (string) $paginacion['base'] . ($filtros ? '?' . http_build_query($filtros) : '');
?>
<div class="d-flex flex-wrap align-items-center gap-3 mb-3">
    <form class="admin-buscador d-flex gap-2 flex-wrap align-items-center" method="get" action="<?= htmlspecialchars((string) $paginacion['base']) ?>" role="search">
        <?php foreach ($filtros as $k => $v): ?>
        <input type="hidden" name="<?= htmlspecialchars((string) $k) ?>" value="<?= htmlspecialchars((string) $v) ?>">
        <?php endforeach; ?>
        <label class="visually-hidden" for="buscarQ">Buscar <?= htmlspecialchars((string) $paginacion['etiqueta']) ?></label>
        <input type="search" id="buscarQ" name="q" class="form-control" value="<?= htmlspecialchars($qActual) ?>"
               placeholder="<?= htmlspecialchars((string) $paginacion['placeholder']) ?>" autocomplete="off">
        <button type="submit" class="btn btn-admin btn-admin--black">Buscar</button>
        <?php if ($qActual !== ''): ?>
        <a class="btn btn-admin btn-admin--white" href="<?= htmlspecialchars($sinBusqueda) ?>">Limpiar</a>
        <?php endif; ?>
    </form>

    <p class="admin-buscador-info ms-md-auto mb-0">
        <?php if ($pgBusca['total'] === 0): ?>
        Sin resultados
        <?php else: ?>
        Mostrando <strong><?= $pgBusca['desde'] ?>–<?= $pgBusca['hasta'] ?></strong> de <strong><?= $pgBusca['total'] ?></strong> <?= htmlspecialchars((string) $paginacion['etiqueta']) ?>
        <?php endif; ?>
        <?php if ($qActual !== ''): ?>para «<?= htmlspecialchars($qActual) ?>»<?php endif; ?>
        <?php if ($pgBusca['paginas'] > 1): ?>· página <strong><?= $pgBusca['pagina'] ?></strong> de <strong><?= $pgBusca['paginas'] ?></strong><?php endif; ?>
    </p>
</div>
