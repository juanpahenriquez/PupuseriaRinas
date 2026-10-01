<?php
// $usuarios, $yo, $total, $admins, $cajeros, $conteoRol, $q, $fRol, $paginacion,
// $flash_ok, $flash_err vienen de admin/usuarios.php
$usuarios  = $usuarios  ?? [];
$yo        = $yo        ?? 0;
$total     = $total     ?? 0;
$admins    = $admins    ?? 0;
$cajeros   = $cajeros   ?? 0;
$q         = $q         ?? '';
$fRol      = $fRol      ?? '';
$conteoRol = $conteoRol ?? ['todos' => 0, 'admin' => 0, 'cajero' => 0];
$paginacion = $paginacion ?? [
    'base' => 'usuarios.php', 'q' => '', 'params' => [], 'total' => 0,
    'porPagina' => 10, 'pagina' => 1, 'etiqueta' => 'usuarios', 'placeholder' => 'Buscar…',
];
$flash_ok  = $flash_ok  ?? null;
$flash_err = $flash_err ?? null;
$rolLabel = ['admin' => 'Admin', 'cajero' => 'Cajero'];
// Enlaces de las píldoras: conservan la búsqueda y cambian el rol (vuelven a la página 1)
$urlFiltro = function (array $extra) use ($q, $fRol): string {
    $params = array_filter(array_merge(['q' => $q, 'rol' => $fRol], $extra), fn($v) => $v !== '' && $v !== null);
    return 'usuarios.php' . ($params ? '?' . http_build_query($params) : '');
};
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

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div class="d-flex gap-2 flex-wrap">
        <a class="admin-filter <?= $fRol === '' ? 'active' : '' ?>" href="<?= htmlspecialchars($urlFiltro(['rol' => ''])) ?>">Todos (<?= (int) $conteoRol['todos'] ?>)</a>
        <a class="admin-filter <?= $fRol === 'admin' ? 'active' : '' ?>" href="<?= htmlspecialchars($urlFiltro(['rol' => 'admin'])) ?>">Admin (<?= (int) $conteoRol['admin'] ?>)</a>
        <a class="admin-filter <?= $fRol === 'cajero' ? 'active' : '' ?>" href="<?= htmlspecialchars($urlFiltro(['rol' => 'cajero'])) ?>">Cajero (<?= (int) $conteoRol['cajero'] ?>)</a>
    </div>
    <button class="btn btn-admin btn-admin--black" data-bs-toggle="modal" data-bs-target="#modalUsuario">+ Nuevo usuario</button>
</div>

<?php include __DIR__ . '/_buscador.php'; ?>

<?php if (empty($usuarios)): ?>
<div class="admin-panel text-center py-5">
    <div class="mx-auto" style="width:64px;height:64px; border:1px dashed #000; display:flex;align-items:center;justify-content:center; background:#FFF6D3;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/></svg></div>
    <h3 class="h6 fw-bold mt-3 mb-1"><?= ($q !== '' || $fRol !== '') ? 'Ningún usuario con este filtro' : 'Sin usuarios aún' ?></h3>
    <p class="small text-muted mb-3"><?= ($q !== '' || $fRol !== '') ? 'Prueba con otra búsqueda o mira todas las cuentas.' : 'Crea la primera cuenta del personal para dar acceso al panel.' ?></p>
    <?php if ($q !== '' || $fRol !== ''): ?>
    <a href="usuarios.php" class="btn btn-admin btn-admin--white">Ver todos</a>
    <?php else: ?>
    <button class="btn btn-admin btn-admin--black" data-bs-toggle="modal" data-bs-target="#modalUsuario">Crear usuario</button>
    <?php endif; ?>
</div>
<?php else: ?>

<div class="admin-panel p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr><th>Usuario</th><th>Rol</th><th>Estado</th><th>Creado</th><th class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): $uId = (int) $u['id']; $esYo = $uId === (int) $yo['id']; ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <span class="admin-user-badge" style="background:<?= $u['rol'] === 'admin' ? '#0B0B45' : '#F59E0B' ?>;color:#fff;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;flex:0 0 auto"><?= htmlspecialchars(strtoupper(mb_substr($u['nombre'], 0, 1))) ?></span>
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($u['nombre']) ?><?= $esYo ? ' <span class="badge-rinas badge-listo">Tú</span>' : '' ?></div>
                                <div class="small text-muted" style="font-size:0.75rem"><?= htmlspecialchars($u['email']) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <?php if ($esYo): ?>
                        <span class="badge-rinas" style="background:#E8E8F5;border:1px solid rgba(11,11,69,0.3);color:#0B0B45"><?= $rolLabel[$u['rol']] ?? htmlspecialchars($u['rol']) ?></span>
                        <?php else: ?>
                        <form method="post" class="d-inline-flex">
                            <?= csrf_input() ?>
                            <input type="hidden" name="action" value="rol">
                            <input type="hidden" name="id" value="<?= $uId ?>">
                            <select name="rol" class="form-select form-select-sm" style="border:1px solid #000;border-radius:0;width:auto;font-size:0.75rem" onchange="this.form.submit()" aria-label="Rol de <?= htmlspecialchars($u['nombre']) ?>">
                                <option value="admin" <?= $u['rol'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                <option value="cajero" <?= $u['rol'] === 'cajero' ? 'selected' : '' ?>>Cajero</option>
                            </select>
                        </form>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge-rinas <?= $u['activo'] ? 'badge-entregado' : 'badge-pendiente' ?>"><?= $u['activo'] ? 'Activo' : 'Inactivo' ?></span></td>
                    <td class="text-muted small"><?= date('d/m/Y', strtotime($u['creado'])) ?></td>
                    <td class="text-end">
                        <?php if (!$esYo): ?>
                        <form method="post" class="d-inline">
                            <?= csrf_input() ?>
                            <input type="hidden" name="action" value="toggle">
                            <input type="hidden" name="id" value="<?= $uId ?>">
                            <button type="submit" class="btn btn-sm" style="border:1px solid #000;background:#fff;font-weight:700;font-size:0.72rem"><?= $u['activo'] ? 'Desactivar' : 'Activar' ?></button>
                        </form>
                        <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar al usuario <?= htmlspecialchars(addslashes($u['nombre'])) ?>?')">
                            <?= csrf_input() ?>
                            <input type="hidden" name="action" value="eliminar">
                            <input type="hidden" name="id" value="<?= $uId ?>">
                            <button type="submit" class="btn btn-sm" style="border:1px solid #000;background:#fff;color:#c0392b;font-weight:700;font-size:0.72rem">Eliminar</button>
                        </form>
                        <?php else: ?>
                        <span class="small text-muted" style="font-size:0.72rem">Sesión actual</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/_paginacion.php'; ?>
<?php endif; ?>

<!-- Modal Nuevo usuario -->
<div class="modal fade" id="modalUsuario" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:480px">
    <div class="modal-content" style="border:1px solid #000;border-radius:0">
      <div class="modal-header" style="background:var(--rinas-crema);border-bottom:1px solid #000">
        <h5 class="modal-title fw-bold" style="letter-spacing:0.04em;text-transform:uppercase">Nuevo usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="crear">
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em" for="usrNombre">Nombre *</label>
                <input name="nombre" id="usrNombre" class="form-control" style="border:1px solid #000;border-radius:0" required minlength="3" placeholder="Nombre completo">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em" for="usrEmail">Correo *</label>
                <input name="email" id="usrEmail" type="email" class="form-control" style="border:1px solid #000;border-radius:0" required placeholder="correo@rinas.com">
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em" for="usrPass">Contraseña * <span class="text-muted" style="letter-spacing:0">(mín 6)</span></label>
                <input name="password" id="usrPass" type="password" class="form-control" style="border:1px solid #000;border-radius:0" required minlength="6" placeholder="••••••">
            </div>
            <div class="mb-1">
                <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em" for="usrRol">Rol *</label>
                <select name="rol" id="usrRol" class="form-select" style="border:1px solid #000;border-radius:0">
                    <option value="cajero" selected>Cajero</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid #000;background:#fff">
            <button type="button" class="btn btn-admin btn-admin--white" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-admin btn-admin--black">Crear usuario</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php if (!empty($flash_err) && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
<script>
(function(){ var m = document.getElementById('modalUsuario'); if(m) new bootstrap.Modal(m).show(); })();
</script>
<?php endif; ?>
