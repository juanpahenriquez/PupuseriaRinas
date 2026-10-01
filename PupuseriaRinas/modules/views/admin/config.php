<?php
// $config, $hayConfig, $flash_ok, $flash_err vienen de admin/config.php
$config = $config ?? [];
foreach (['nombre', 'whatsapp', 'telefono', 'direccion', 'maps_query', 'horario_texto', 'instagram', 'facebook', 'logo'] as $claveCfg) {
    $config[$claveCfg] = (string) ($config[$claveCfg] ?? '');
}
$hayConfig = $hayConfig ?? false;
$flash_ok = $flash_ok ?? null;
$flash_err = $flash_err ?? null;
$waLink = $config['whatsapp'] !== '' ? 'https://wa.me/' . $config['whatsapp'] : null;
$mapaQ = $config['maps_query'] !== '' ? $config['maps_query'] : ($config['direccion'] !== '' ? $config['direccion'] : 'Pupusería Rinas, El Salvador');
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

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <form method="post" enctype="multipart/form-data" class="admin-panel h-100">
            <?= csrf_input() ?>
            <input type="hidden" name="action" value="guardar">
            <h2 class="admin-panel-title mb-3">Datos del negocio</h2>
            <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase label-admin" for="cfgDir">Dirección a mostrar</label>
                <input id="cfgDir" name="direccion" class="form-control" style="border:1px solid #000;border-radius:0" value="<?= htmlspecialchars($config['direccion']) ?>" placeholder="Ej: Av. Central #123, San Salvador">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase label-admin" for="cfgMap">Query para Google Maps</label>
                <input id="cfgMap" name="maps_query" class="form-control" style="border:1px solid #000;border-radius:0" value="<?= htmlspecialchars($config['maps_query']) ?>" placeholder="Ej: Av. Central, San Salvador">
                <div class="small text-muted mt-1" style="font-size:0.72rem">Si lo dejas vacío se usa la dirección.</div>
            </div>
            <div class="row g-3 mb-3">
                <div class="col-6">
                    <label class="form-label fw-bold small text-uppercase label-admin" for="cfgTel">Teléfono mostrado</label>
                    <input id="cfgTel" name="telefono" class="form-control" style="border:1px solid #000;border-radius:0" value="<?= htmlspecialchars($config['telefono']) ?>" placeholder="7000-0000">
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small text-uppercase label-admin" for="cfgWa">WhatsApp (solo dígitos)</label>
                    <input id="cfgWa" name="whatsapp" class="form-control" style="border:1px solid #000;border-radius:0" value="<?= htmlspecialchars($config['whatsapp']) ?>" placeholder="50370000000" inputmode="numeric">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase label-admin" for="cfgLogo">Logo</label>
                <div class="d-flex align-items-center gap-3">
                    <div class="admin-logo-preview">
                        <?php if ($config['logo'] !== ''): ?>
                        <img src="../<?= htmlspecialchars($config['logo']) ?>" alt="Logo actual" style="max-width:100%;max-height:100%;object-fit:contain">
                        <?php else: ?>
                        <span class="small text-muted">Sin logo</span>
                        <?php endif; ?>
                    </div>
                    <input id="cfgLogo" name="logo" type="file" accept="image/jpeg,image/png,image/webp,image/svg+xml" class="form-control form-control-sm" style="border:1px solid #000;border-radius:0;max-width:230px">
                </div>
                <div class="small text-muted mt-1" style="font-size:0.72rem">jpg/png/webp/svg máx 2MB.</div>
            </div>
            <button type="submit" class="btn btn-admin btn-admin--black">Guardar cambios</button>
        </form>
    </div>
    <div class="col-12 col-lg-5">
        <div class="admin-panel h-100">
            <div class="admin-panel-head">
                <h2 class="admin-panel-title">Preview</h2>
                <span class="badge-rinas <?= $hayConfig ? 'badge-entregado' : 'badge-pendiente' ?>"><?= $hayConfig ? 'Guardado' : 'Sin configurar' ?></span>
            </div>
            <div style="border:1px solid #000;background:#fff">
                <iframe
                    src="https://maps.google.com/maps?q=<?= urlencode($mapaQ) ?>&z=15&output=embed"
                    style="width:100%;height:200px;border:0;display:block"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Mapa de Pupusería Rinas"></iframe>
                <div class="p-3">
                    <div class="fw-bold mb-1">Pupusería Rinas</div>
                    <div class="small text-muted mb-2">
                        📍 <?= $config['direccion'] !== '' ? htmlspecialchars($config['direccion']) : 'Sin dirección configurada' ?><br>
                        📞 <?= $config['telefono'] !== '' ? htmlspecialchars($config['telefono']) : 'Sin teléfono configurado' ?>
                    </div>
                    <?php if ($waLink): ?>
                    <a href="<?= htmlspecialchars($waLink) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-admin btn-admin--black">WhatsApp →</a>
                    <?php else: ?>
                    <span class="small text-muted" style="font-size:0.72rem">Agrega el WhatsApp para mostrar el botón.</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
