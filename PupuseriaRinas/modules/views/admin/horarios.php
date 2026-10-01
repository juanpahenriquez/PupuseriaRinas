<?php
// $horarios, $dias, $diaHoy, $hayAlgo, $flash_ok, $flash_err vienen de admin/horarios.php
$horarios = $horarios ?? [];
$dias     = $dias     ?? [];
$diaHoy   = $diaHoy   ?? (int) date('N');
$hayAlgo  = $hayAlgo  ?? false;
$flash_ok = $flash_ok ?? null;
$flash_err = $flash_err ?? null;
?>
<?php if (!empty($flash_ok)): ?>
<div class="alert d-flex align-items-center gap-2" role="alert" style="border:1px solid #000;border-radius:0;background:#E6F4EA;color:#0a7a42;font-weight:700">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
    <?= htmlspecialchars($flash_ok) ?>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<?php if (!empty($flash_err)): ?>
<div class="alert d-flex align-items-center gap-2" role="alert" style="border:1px solid #000;border-radius:0;background:#FFF4DC;color:#8a5a00;font-weight:700">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
    <?= htmlspecialchars($flash_err) ?>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="admin-panel mb-3">
    <div class="admin-panel-head">
        <h2 class="admin-panel-title">Vista previa</h2>
        <span class="badge-rinas <?= $hayAlgo ? 'badge-entregado' : 'badge-pendiente' ?>"><?= $hayAlgo ? 'Configurado' : 'Sin definir' ?></span>
    </div>
    <?php if (!$hayAlgo): ?>
    <div class="text-center py-4" style="border:1px dashed #000; background:#FFF6D3;">
        <span class="small text-muted">Sin horarios configurados — completa la tabla y se mostrará aquí y en Ubicación</span>
    </div>
    <?php else: ?>
    <div class="semana-grid mb-0">
        <?php foreach ($dias as $d => $nombre): $h = $horarios[$d] ?? null; $r = rango_dia($h); ?>
        <div class="semana-dia <?= $d === $diaHoy ? 'es-hoy' : '' ?>">
            <div class="semana-nombre"><?= $nombre ?></div>
            <div class="semana-hora <?= ($h && (!empty($h['cerrado']) || $r === '')) ? 'cerrado' : '' ?>">
                <?= $r !== '' ? htmlspecialchars($r) : (!empty($h['cerrado']) ? 'Cerrado' : 'Sin definir') ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<form method="post" class="admin-panel">
    <?= csrf_input() ?>
    <input type="hidden" name="action" value="guardar">
    <div class="admin-panel-head">
        <h2 class="admin-panel-title">Editar horarios</h2>
        <button type="submit" class="btn btn-sm btn-admin btn-admin--black">Guardar</button>
    </div>
    <div class="table-responsive">
        <table class="table admin-table mb-0">
            <thead><tr><th>Día</th><th>Mañana apertura</th><th>Mañana cierre</th><th>Tarde apertura</th><th>Tarde cierre</th><th>Cerrado</th></tr></thead>
            <tbody>
                <?php foreach ($dias as $d => $nombre):
                    $h = $horarios[$d] ?? [];
                    $v = fn($k) => !empty($h[$k]) ? substr($h[$k], 0, 5) : '';
                ?>
                <tr>
                    <td class="fw-bold"><?= $nombre ?><?= $d === $diaHoy ? ' <span class="badge-rinas badge-listo">Hoy</span>' : '' ?></td>
                    <td><input type="time" name="h[<?= $d ?>][am_apertura]" value="<?= htmlspecialchars($v('am_apertura')) ?>" class="form-control form-control-sm" aria-label="<?= $nombre ?> mañana apertura"></td>
                    <td><input type="time" name="h[<?= $d ?>][am_cierre]" value="<?= htmlspecialchars($v('am_cierre')) ?>" class="form-control form-control-sm" aria-label="<?= $nombre ?> mañana cierre"></td>
                    <td><input type="time" name="h[<?= $d ?>][pm_apertura]" value="<?= htmlspecialchars($v('pm_apertura')) ?>" class="form-control form-control-sm" aria-label="<?= $nombre ?> tarde apertura"></td>
                    <td><input type="time" name="h[<?= $d ?>][pm_cierre]" value="<?= htmlspecialchars($v('pm_cierre')) ?>" class="form-control form-control-sm" aria-label="<?= $nombre ?> tarde cierre"></td>
                    <td class="text-center"><input type="checkbox" name="h[<?= $d ?>][cerrado]" value="1" <?= !empty($h['cerrado']) ? 'checked' : '' ?> aria-label="<?= $nombre ?> cerrado"></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="p-3 d-flex justify-content-end" style="border-top:1px solid #000;background:#fff">
        <button type="submit" class="btn btn-admin btn-admin--black">Guardar cambios</button>
    </div>
</form>
