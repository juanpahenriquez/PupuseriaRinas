<?php
/* ══════════════════════════════════════════════════════════════════
   DATOS DEL CONTROLADOR (admin/pedidos.php)
   $pedidos, $contados, $fTipo, $fEstado, $q, $productosModal, $paginacion,
   $flash_ok, $flash_err y $estados — el catálogo de estados, que trae la
   etiqueta, el badge, la clase del <select> y el icon/título del toast.
   Ningún estado se escribe a mano en esta vista.
   ══════════════════════════════════════════════════════════════════ */
$pedidos       = $pedidos       ?? [];
$contados      = $contados      ?? [];
$fTipo         = $fTipo         ?? '';
$fEstado       = $fEstado       ?? '';
$q             = $q             ?? '';   // lo define el controlador (q_get), el linter no lo ve
$productosModal = $productosModal ?? [];
$estados       = $estados       ?? [];

/* Lo único de cada tipo de entrega que define la vista: 'filtro' para las
   píldoras y la columna Tipo, 'opcion' para el <select> del formulario y
   el modal de detalle. Los ids los valida el controlador. */
$tipos = [
    'llevar'  => ['filtro' => 'Llevar',  'opcion' => 'Para llevar'],
    'recoger' => ['filtro' => 'Recoger', 'opcion' => 'Recoger en tienda'],
];

$flash_ok      = $flash_ok      ?? null;
$flash_err     = $flash_err     ?? null;
$flash_toast   = $_SESSION['flash_toast'] ?? null;
unset($_SESSION['flash_toast']);

/* ══════════════════════════════════════════════════════════════════
   AYUDAS DE LA VISTA
   ══════════════════════════════════════════════════════════════════ */

// El contador de resultados se pinta abajo, en el pie del panel
$pieContador = !empty($pedidos);

/**
 * Líneas del pedido para el modal de detalle.
 * Viene ordenadas del controlador ($p['items']); si no llegan (consultas
 * antiguas, datos de prueba) se deducen del texto "3× Revuelta, 2× Queso".
 * El corte va justo antes de la cifra para no partir nombres con coma.
 */
$lineasDe = function (array $p): array {
    $lineas = [];
    foreach (($p['items'] ?? []) as $it) {
        $lineas[] = [
            'nombre'   => (string) ($it['nombre'] ?? ''),
            'cantidad' => (int) ($it['cantidad'] ?? 1),
            'precio'   => isset($it['precio']) && $it['precio'] !== null ? (float) $it['precio'] : null,
        ];
    }
    if ($lineas) {
        return $lineas;
    }
    $detalle = trim((string) ($p['detalle'] ?? ''));
    if ($detalle === '' || $detalle === '—') {
        return [];
    }
    foreach ((preg_split('/,\s*(?=\d+\s*[x×]\s*)/iu', $detalle) ?: []) as $parte) {
        $parte = trim($parte);
        if ($parte === '') {
            continue;
        }
        if (preg_match('/^(\d+)\s*[x×]\s*(.+)$/u', $parte, $m)) {
            $lineas[] = ['nombre' => trim($m[2]), 'cantidad' => (int) $m[1], 'precio' => null];
        } else {
            $lineas[] = ['nombre' => $parte, 'cantidad' => 1, 'precio' => null];
        }
    }
    return $lineas;
};

$url = function (array $extra): string {
    $q = array_merge($_GET, $extra);
    unset($q['p']); // cambiar de filtro siempre vuelve a la página 1
    $q = array_filter($q, fn($v) => $v !== '' && $v !== null);
    return 'pedidos.php' . ($q ? '?' . http_build_query($q) : '');
};
$backActual = basename($_SERVER['PHP_SELF'] ?? 'pedidos.php') . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
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

<!-- Fila 1: filtro por tipo + acción principal -->
<div class="pedidos-toolbar">
    <div class="pedidos-grupo">
        <span class="pedidos-etiqueta">Tipo</span>
        <a class="admin-filter <?= $fTipo === '' && $fEstado === '' ? 'active' : '' ?>" href="<?= htmlspecialchars($url(['tipo' => '', 'estado' => ''])) ?>">Todos (<?= (int) $contados['total'] ?>)</a>
        <?php foreach (['recoger', 'llevar'] as $t): ?>
        <a class="admin-filter <?= $fTipo === $t && $fEstado === '' ? 'active' : '' ?>" href="<?= htmlspecialchars($url(['tipo' => $t, 'estado' => ''])) ?>"><?= $tipos[$t]['filtro'] ?> (<?= (int) ($contados[$t] ?? 0) ?>)</a>
        <?php endforeach; ?>
    </div>
    <a class="btn btn-admin btn-admin--black pedidos-nuevo" href="pos.php">+ Nuevo pedido</a>
</div>

<!-- Fila 2: filtro por estado + buscador -->
<div class="pedidos-toolbar pedidos-toolbar--bajo">
    <div class="pedidos-grupo">
        <span class="pedidos-etiqueta">Estado</span>
        <?php foreach ($estados as $eId => $eCfg): ?>
        <a class="admin-filter <?= $fEstado === $eId ? 'active' : '' ?>" href="<?= htmlspecialchars($url(['estado' => $eId, 'tipo' => ''])) ?>"><?= $eCfg['label'] ?> (<?= (int) ($contados[$eId] ?? 0) ?>)</a>
        <?php endforeach; ?>
    </div>
    <?php include __DIR__ . '/_buscador.php'; ?>
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
    <a class="btn btn-admin btn-admin--black" href="pos.php">Crear pedido</a>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="admin-panel p-0 pedidos-panel">
    <div class="table-responsive">
        <table class="table admin-table pedidos-tabla mb-0 align-middle">
            <thead>
                <tr>
                    <th>Folio</th>
                    <th>Cliente</th>
                    <th>Tipo</th>
                    <th class="text-center">Detalle</th>
                    <th class="text-end">Total</th>
                    <th>Estado</th>
                    <th>Hora</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p):
                    $pId = (int) $p['id'];
                    $pEstado = (string) $p['estado'];
                    // Columnas ENUM: el catálogo siempre resuelve; el plan B
                    // solo evita que la fila se rompa si algún día no lo hace.
                    $est   = $estados[$pEstado] ?? ['label' => $pEstado, 'badge' => 'badge-pendiente', 'clase' => 'pedidos-estado--pendiente'];
                    $tipo  = $tipos[$p['tipo']] ?? ['filtro' => htmlspecialchars($p['tipo']), 'opcion' => (string) $p['tipo']];
                    $folio = $p['folio'] !== '' ? $p['folio'] : '#' . $pId;
                ?>
                <?php
                    $lineas = $lineasDe($p);
                    // Ficha que viaja al modal; las etiquetas salen del catálogo.
                    $payload = [
                        'id'          => $pId,
                        'folio'       => $folio,
                        'cliente'     => (string) $p['cliente'],
                        'telefono'    => (string) $p['telefono'],
                        'notas'       => (string) $p['notas'],
                        'tipo'        => (string) $p['tipo'],
                        'tipoLabel'   => $tipo['opcion'],
                        'estado'      => $pEstado,
                        'estadoLabel' => $est['label'],
                        'estadoClase' => $est['badge'],
                        'fecha'       => date('d/m/Y H:i', strtotime($p['creado'])),
                        'total'       => (float) $p['total'],
                        'lineas'      => $lineas,
                    ];
                    $detalleJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
                    if ($detalleJson === false) {
                        // Nunca debería pasar: si una fila viniera con bytes inválidos
                        // se sustituyen para que al menos el modal se pueda abrir.
                        $detalleJson = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
                    }
                ?>
                <tr>
                    <td><span class="pedidos-folio"><?= htmlspecialchars($folio) ?></span></td>
                    <td>
                        <div class="pedidos-cliente"><?= htmlspecialchars($p['cliente']) ?></div>
                        <?php if ($p['telefono'] !== '' || $p['notas'] !== ''): ?>
                        <div class="pedidos-datos">
                            <?php if ($p['telefono'] !== ''): ?><span class="pedidos-tel" title="Teléfono"><i class="fa-solid fa-phone" aria-hidden="true"></i><?= htmlspecialchars($p['telefono']) ?></span><?php endif; ?>
                            <?php if ($p['notas'] !== ''): ?><span class="pedidos-nota" title="<?= htmlspecialchars($p['notas']) ?>"><i class="fa-regular fa-note-sticky" aria-hidden="true"></i><?= htmlspecialchars(mb_strimwidth($p['notas'], 0, 24, '…')) ?></span><?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php $esLlevar = $p['tipo'] === 'llevar'; ?>
                        <span class="pedidos-tipo"><i class="<?= $esLlevar ? 'fa-solid fa-bag-shopping' : 'fa-solid fa-store' ?>" aria-hidden="true"></i><?= $tipo['filtro'] ?></span>
                    </td>
                    <td class="text-center">
                        <button type="button" class="pedidos-ojo" data-detalle="<?= htmlspecialchars($detalleJson, ENT_QUOTES) ?>"
                                title="Ver detalle del pedido <?= htmlspecialchars($folio) ?>"
                                aria-label="Ver detalle del pedido <?= htmlspecialchars($folio) ?>">
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                        </button>
                    </td>
                    <td class="pedidos-total">$<?= number_format((float) $p['total'], 2) ?></td>
                    <td>
                        <form method="post" class="d-inline">
                            <?= csrf_input() ?>
                            <input type="hidden" name="action" value="estado">
                            <input type="hidden" name="id" value="<?= $pId ?>">
                            <input type="hidden" name="back" value="<?= htmlspecialchars($backActual) ?>">
                            <select name="estado" class="form-select form-select-sm pedidos-estado <?= $est['clase'] ?>" onchange="this.form.submit()" aria-label="Cambiar estado del pedido <?= $pId ?>">
                                <?php foreach ($estados as $eId => $eCfg): ?>
                                <option value="<?= $eId ?>" <?= $pEstado === $eId ? 'selected' : '' ?>><?= $eCfg['label'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td><span class="pedidos-hora" title="<?= htmlspecialchars(date('d/m/Y H:i', strtotime($p['creado']))) ?>"><i class="fa-regular fa-clock" aria-hidden="true"></i><?= hace($p['creado']) ?></span></td>
                    <td class="text-end">
                        <form method="post" class="d-inline" onsubmit="return confirm('¿Eliminar el pedido <?= htmlspecialchars(addslashes($folio)) ?>?')">
                            <?= csrf_input() ?>
                            <input type="hidden" name="action" value="eliminar">
                            <input type="hidden" name="id" value="<?= $pId ?>">
                            <input type="hidden" name="back" value="<?= htmlspecialchars($backActual) ?>">
                            <button type="submit" class="btn btn-sm pedidos-borrar" aria-label="Eliminar pedido <?= htmlspecialchars($folio) ?>"><i class="fa-regular fa-trash-can" aria-hidden="true"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php include __DIR__ . '/_paginacion.php'; ?>
</div>
<?php endif; ?>

<!-- Modal detalle del pedido -->
<div class="modal fade" id="modalDetalle" tabindex="-1" aria-hidden="true" aria-labelledby="mdTitulo">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:540px">
    <div class="modal-content pedidos-modal">
      <div class="pedidos-modal__cabecera">
        <div class="pedidos-modal__titulos">
          <span class="pedidos-modal__cejo">Detalle del pedido</span>
          <h5 class="modal-title" id="mdTitulo">—</h5>
        </div>
        <span class="badge-rinas" id="mdEstado">—</span>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <dl class="pedidos-ficha">
          <div><dt>Cliente</dt><dd id="mdCliente">—</dd></div>
          <div><dt>Teléfono</dt><dd id="mdTel">—</dd></div>
          <div><dt>Entrega</dt><dd id="mdTipo">—</dd></div>
          <div><dt>Fecha</dt><dd id="mdFecha">—</dd></div>
        </dl>

        <p class="pedidos-modal__nota" id="mdNotas" hidden></p>

        <table class="pedidos-lineas">
          <thead>
            <tr><th>Cant.</th><th>Producto</th><th class="text-end">Importe</th></tr>
          </thead>
          <tbody id="mdLineas"></tbody>
          <tfoot>
            <tr><td colspan="2">Total</td><td class="text-end" id="mdTotal">$0.00</td></tr>
          </tfoot>
        </table>
      </div>
      <div class="modal-footer" style="border-top:1px solid #000">
        <button type="button" class="btn btn-admin btn-admin--white" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

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
                        <?php foreach (['llevar', 'recoger'] as $t): ?>
                        <option value="<?= $t ?>" <?= $t === 'recoger' ? 'selected' : '' ?>><?= $tipos[$t]['opcion'] ?></option>
                        <?php endforeach; ?>
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
  /* Modal de detalle: cada fila trae su ficha en data-detalle (JSON) y aquí
     solo se vuelca al modal. Sin llamadas al servidor ni plantillas extra. */
  const md = document.getElementById('modalDetalle');
  if (!md || typeof bootstrap === 'undefined') return;
  const modal = new bootstrap.Modal(md);
  const $ = id => document.getElementById(id);
  const money = n => '$' + (Number(n) || 0).toFixed(2);
  const esc = s => String(s == null ? '' : s).replace(/[&<>"']/g, c =>
    ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  document.querySelectorAll('.pedidos-ojo').forEach(btn => {
    btn.addEventListener('click', () => {
      let d;
      try { d = JSON.parse(btn.getAttribute('data-detalle')); } catch (e) { return; }
      if (!d) return;

      $('mdTitulo').textContent = d.folio || ('#' + d.id);
      $('mdCliente').textContent = d.cliente || '—';
      $('mdTel').textContent = d.telefono || 'Sin teléfono';
      $('mdTipo').textContent = d.tipoLabel || 'Recoger en tienda';
      $('mdFecha').textContent = d.fecha || '—';

      const estado = $('mdEstado');
      estado.className = 'badge-rinas ' + (d.estadoClase || 'badge-pendiente');
      estado.textContent = d.estadoLabel || '—';

      const notas = $('mdNotas');
      notas.hidden = !d.notas;
      notas.innerHTML = d.notas
        ? '<i class="fa-regular fa-note-sticky" aria-hidden="true"></i>' + esc(d.notas)
        : '';

      const lineas = Array.isArray(d.lineas) ? d.lineas : [];
      $('mdLineas').innerHTML = lineas.length
        ? lineas.map(l => {
            const importe = (l.precio == null) ? null : (Number(l.precio) * (Number(l.cantidad) || 1));
            return '<tr><td class="pedidos-lineas__cant">' + (Number(l.cantidad) || 1) + '×</td>'
                 + '<td>' + esc(l.nombre) + '</td>'
                 + '<td class="text-end">' + (importe == null ? '—' : money(importe)) + '</td></tr>';
          }).join('')
        : '<tr><td colspan="3" class="pedidos-lineas__vacia">Sin productos registrados</td></tr>';

      $('mdTotal').textContent = money(d.total);
      modal.show();
    });
  });
})();
</script>

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

  <?php if (!empty($flash_err) && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'): ?>
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
