<?php
// $pedidos, $contados, $fTipo, $fEstado, $productosModal, $flash_ok, $flash_err, $estados
// vienen del controlador admin/pedidos.php
$pedidos       = $pedidos       ?? [];
$contados      = $contados      ?? ['total' => 0, 'recoger' => 0, 'llevar' => 0, 'pendiente' => 0, 'cocina' => 0, 'listo' => 0, 'entregado' => 0];
$fTipo         = $fTipo         ?? '';
$fEstado       = $fEstado       ?? '';
$q             = $q             ?? '';   // lo define el controlador (q_get), el linter no lo ve
$productosModal = $productosModal ?? [];
$estados       = $estados       ?? ['pendiente' => 'Pendiente', 'cocina' => 'En cocina', 'listo' => 'Listo', 'entregado' => 'Entregado'];
$flash_ok      = $flash_ok      ?? null;
$flash_err     = $flash_err     ?? null;
$flash_toast   = $_SESSION['flash_toast'] ?? null;
unset($_SESSION['flash_toast']);
$estadoBadge = ['pendiente' => 'badge-pendiente', 'cocina' => 'badge-cocina', 'listo' => 'badge-listo', 'entregado' => 'badge-entregado'];
$estadoLabel = ['pendiente' => 'Pendiente', 'cocina' => 'En cocina', 'listo' => 'Listo', 'entregado' => 'Entregado'];
$tipoLabel = ['llevar' => 'Llevar', 'recoger' => 'Recoger'];
$url = function (array $extra): string {
    $q = array_merge($_GET, $extra);
    unset($q['p']); // cambiar de filtro siempre vuelve a la página 1
    $q = array_filter($q, fn($v) => $v !== '' && $v !== null);
    return 'pedidos.php' . ($q ? '?' . http_build_query($q) : '');
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

<div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
    <a class="admin-filter <?= $fTipo === '' && $fEstado === '' ? 'active' : '' ?>" href="<?= htmlspecialchars($url(['tipo' => '', 'estado' => ''])) ?>">Todos (<?= (int) $contados['total'] ?>)</a>
    <a class="admin-filter <?= $fTipo === 'recoger' && $fEstado === '' ? 'active' : '' ?>" href="<?= htmlspecialchars($url(['tipo' => 'recoger', 'estado' => ''])) ?>">Recoger (<?= (int) $contados['recoger'] ?>)</a>
    <a class="admin-filter <?= $fTipo === 'llevar' && $fEstado === '' ? 'active' : '' ?>" href="<?= htmlspecialchars($url(['tipo' => 'llevar', 'estado' => ''])) ?>">Llevar (<?= (int) $contados['llevar'] ?>)</a>
    <span class="ms-auto d-flex gap-2 flex-wrap">
        <?php foreach (['pendiente', 'cocina', 'listo', 'entregado'] as $e): ?>
        <a class="admin-filter <?= $fEstado === $e ? 'active' : '' ?>" href="<?= htmlspecialchars($url(['estado' => $e, 'tipo' => ''])) ?>"><?= $estadoLabel[$e] ?> (<?= (int) $contados[$e] ?>)</a>
        <?php endforeach; ?>
    </span>
</div>

<?php include __DIR__ . '/_buscador.php'; ?>

<div class="d-flex justify-content-end mb-3">
    <button class="btn btn-admin btn-admin--black" data-bs-toggle="modal" data-bs-target="#modalPedido" <?= empty($productosModal) ? 'disabled' : '' ?>>+ Nuevo pedido</button>
</div>

<?php if (empty($pedidos)): ?>
<div class="admin-panel text-center py-5">
    <div class="mx-auto" style="width:64px;height:64px; border:1px dashed #000; display:flex;align-items:center;justify-content:center; background:#FFF6D3;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6h6"/><path d="M9 10h6"/><path d="M9 14h6"/><rect x="4" y="3" width="16" height="18" rx="2"/></svg></div>
    <h3 class="h6 fw-bold mt-3 mb-1"><?= ($fTipo || $fEstado || $q !== '') ? 'Ningún pedido con este filtro' : 'Sin pedidos aún' ?></h3>
    <p class="small text-muted mb-3"><?= ($fTipo || $fEstado || $q !== '') ? 'Prueba con otra búsqueda o mira todos los pedidos.' : 'Los pedidos (llevar / recoger) aparecerán aquí en cuanto se creen.' ?></p>
    <?php if ($fTipo || $fEstado || $q !== ''): ?>
    <a href="pedidos.php" class="btn btn-admin btn-admin--white">Ver todos</a>
    <?php elseif (empty($productosModal)): ?>
    <a href="productos.php" class="btn btn-admin btn-admin--black">Crear productos primero</a>
    <?php else: ?>
    <button class="btn btn-admin btn-admin--black" data-bs-toggle="modal" data-bs-target="#modalPedido">Crear pedido</button>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="admin-panel p-0 overflow-hidden">
    <div class="table-responsive">
        <table class="table admin-table mb-0 align-middle">
            <thead>
                <tr><th>Folio</th><th>Cliente</th><th>Tipo</th><th>Detalle</th><th>Total</th><th>Estado</th><th>Hora</th><th class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p): $pId = (int) $p['id']; ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($p['folio'] !== '' ? $p['folio'] : '#' . $pId) ?></td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($p['cliente']) ?></div>
                        <?php if ($p['telefono'] !== ''): ?><div class="small text-muted" style="font-size:0.75rem"><?= htmlspecialchars($p['telefono']) ?></div><?php endif; ?>
                        <?php if ($p['notas'] !== ''): ?><div class="small text-muted" style="font-size:0.75rem" title="<?= htmlspecialchars($p['notas']) ?>">📝 <?= htmlspecialchars(mb_strimwidth($p['notas'], 0, 40, '…')) ?></div><?php endif; ?>
                    </td>
                    <td><?= $tipoLabel[$p['tipo']] ?? htmlspecialchars($p['tipo']) ?></td>
                    <td class="text-muted"><?= htmlspecialchars($p['detalle']) ?></td>
                    <td class="fw-bold">$<?= number_format((float) $p['total'], 2) ?></td>
                    <td><span class="badge-rinas <?= $estadoBadge[$p['estado']] ?? 'badge-pendiente' ?>"><?= $estadoLabel[$p['estado']] ?? htmlspecialchars($p['estado']) ?></span></td>
                    <td class="text-muted small" title="<?= htmlspecialchars(date('d/m/Y H:i', strtotime($p['creado']))) ?>"><?= hace($p['creado']) ?></td>
                    <td class="text-end">
                        <form method="post" class="d-inline-flex gap-1 align-items-center">
                            <?= csrf_input() ?>
                            <input type="hidden" name="action" value="estado">
                            <input type="hidden" name="id" value="<?= $pId ?>">
                            <input type="hidden" name="back" value="<?= htmlspecialchars(basename($_SERVER['PHP_SELF']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')) ?>">
                            <select name="estado" class="form-select form-select-sm" style="border:1px solid #000;border-radius:0;width:auto;font-size:0.75rem" onchange="this.form.submit()" aria-label="Cambiar estado del pedido <?= $pId ?>">
                                <?php foreach ($estados as $e): ?>
                                <option value="<?= $e ?>" <?= $p['estado'] === $e ? 'selected' : '' ?>><?= $estadoLabel[$e] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                        <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar el pedido <?= htmlspecialchars(addslashes($p['folio'] ?: '#' . $pId)) ?>?')">
                            <?= csrf_input() ?>
                            <input type="hidden" name="action" value="eliminar">
                            <input type="hidden" name="id" value="<?= $pId ?>">
                            <input type="hidden" name="back" value="<?= htmlspecialchars(basename($_SERVER['PHP_SELF']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '')) ?>">
                            <button type="submit" class="btn btn-sm" style="border:1px solid #000;background:#fff;color:#c0392b;font-weight:700" aria-label="Eliminar pedido <?= $pId ?>">✕</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/_paginacion.php'; ?>
<?php endif; ?>

<!-- Modal Nuevo pedido -->
<div class="modal fade" id="modalPedido" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:600px">
    <div class="modal-content" style="border:1px solid #000;border-radius:0">
      <div class="modal-header" style="background:var(--rinas-crema);border-bottom:1px solid #000">
        <h5 class="modal-title fw-bold" style="letter-spacing:0.04em;text-transform:uppercase">Nuevo pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" id="formPedido">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="crear">
        <div class="modal-body">
            <div class="row g-3 mb-3">
                <div class="col-12 col-sm-6">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em" for="pedCliente">Cliente *</label>
                    <input name="cliente" id="pedCliente" class="form-control" style="border:1px solid #000;border-radius:0" placeholder="Nombre del cliente" required>
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em" for="pedTel">Teléfono</label>
                    <input name="telefono" id="pedTel" class="form-control" style="border:1px solid #000;border-radius:0" placeholder="7000-0000">
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em" for="pedTipo">Tipo *</label>
                    <select name="tipo" id="pedTipo" class="form-select" style="border:1px solid #000;border-radius:0">
                        <option value="llevar">Para llevar</option>
                        <option value="recoger" selected>Recoger en tienda</option>
                    </select>
                </div>
                <div class="col-12 col-sm-6">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em" for="pedNotas">Notas</label>
                    <input name="notas" id="pedNotas" class="form-control" style="border:1px solid #000;border-radius:0" placeholder="Sin cebolla, etc.">
                </div>
            </div>

            <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em">Productos *</label>
            <div id="lineasWrap" class="d-flex flex-column gap-2">
                <div class="d-flex gap-2 linea-pedido">
                    <select name="producto_id[]" class="form-select form-select-sm producto-select" style="border:1px solid #000;border-radius:0;flex:1" aria-label="Producto">
                        <?php foreach ($productosModal as $pm): ?>
                        <option value="<?= (int) $pm['id'] ?>" data-precio="<?= number_format((float) $pm['precio'], 2, '.', '') ?>"><?= htmlspecialchars($pm['nombre']) ?> — $<?= number_format((float) $pm['precio'], 2) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="cantidad[]" class="form-control form-control-sm cant-pedido" value="1" min="1" style="border:1px solid #000;border-radius:0;width:74px" aria-label="Cantidad">
                    <button type="button" class="btn btn-sm quitar-linea" style="border:1px solid #000;background:#fff;color:#c0392b;font-weight:700" aria-label="Quitar línea">✕</button>
                </div>
            </div>
            <button type="button" id="addLinea" class="btn btn-sm mt-2" style="border:1px dashed #000;background:#fff;font-weight:700">+ Agregar producto</button>

            <div class="d-flex justify-content-between align-items-center mt-3 p-2" style="border:1px solid #000;background:#FFF6D3">
                <span class="fw-bold small text-uppercase">Total</span>
                <span class="fw-bold" id="pedidoTotal" style="font-size:1.15rem">$0.00</span>
            </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid #000;background:#fff">
            <button type="button" class="btn btn-admin btn-admin--white" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-admin btn-admin--black">Guardar pedido</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  const wrap = document.getElementById('lineasWrap');
  const totalEl = document.getElementById('pedidoTotal');
  const addBtn = document.getElementById('addLinea');
  if(!wrap || !totalEl) return;

  function recalc(){
    let t = 0;
    wrap.querySelectorAll('.linea-pedido').forEach(lin=>{
      const sel = lin.querySelector('.producto-select');
      const cant = parseInt(lin.querySelector('.cant-pedido').value, 10) || 0;
      const precio = parseFloat(sel.options[sel.selectedIndex].dataset.precio) || 0;
      t += precio * Math.max(cant, 0);
    });
    totalEl.textContent = '$' + t.toFixed(2);
  }

  wrap.addEventListener('input', recalc);
  wrap.addEventListener('change', recalc);
  wrap.addEventListener('click', e=>{
    const q = e.target.closest('.quitar-linea');
    if(!q) return;
    if(wrap.querySelectorAll('.linea-pedido').length > 1){
      q.closest('.linea-pedido').remove();
    } else {
      wrap.querySelector('.cant-pedido').value = 1;
    }
    recalc();
  });

  if(addBtn){
    addBtn.addEventListener('click', ()=>{
      const first = wrap.querySelector('.linea-pedido');
      const clone = first.cloneNode(true);
      clone.querySelector('.cant-pedido').value = 1;
      wrap.appendChild(clone);
      recalc();
    });
  }

  <?php if (!empty($flash_err) && $_SERVER['REQUEST_METHOD'] === 'POST'): ?>
  var mP = document.getElementById('modalPedido');
  if(mP) new bootstrap.Modal(mP).show();
  <?php endif; ?>

  recalc();
})();
</script>

<?php if ($flash_toast): ?>
<script src="<?= LINK_SWEETALERT_JS ?>"></script>
<script>
(function(){
  if (typeof Swal === 'undefined') return;
  const t = <?= json_encode(json_decode((string) $flash_toast, true) ?: ['icon'=>'info','title'=>'','text'=>''], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
  Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
      toast.onmouseenter = Swal.stopTimer;
      toast.onmouseleave = Swal.resumeTimer;
    }
  }).fire({
    icon: t.icon,
    title: t.title,
    text: t.text || undefined
  });
})();
</script>
<?php endif; ?>
