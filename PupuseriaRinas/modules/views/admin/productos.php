<?php
/**
 * Vista de productos. El controlador admin/productos.php inyecta:
 *   $productos, $total, $activos, $q, $cat, $conteoCat, $paginacion
 * Los ?? son valores por defecto: no pisan lo que ya venga del controlador.
 */
$productos  = $productos  ?? [];
$total      = $total      ?? 0;
$activos    = $activos    ?? 0;
$q          = $q          ?? '';
$cat        = $cat        ?? '';
$conteoCat  = $conteoCat  ?? ['Todas' => 0, 'Pupusas' => 0, 'Bebidas' => 0, 'Complementos' => 0];
$paginacion = $paginacion ?? [
    'base' => 'productos.php', 'q' => '', 'params' => [], 'total' => 0,
    'porPagina' => 12, 'pagina' => 1, 'etiqueta' => 'productos', 'placeholder' => 'Buscar…',
];

$urlActualProd = basename($_SERVER['PHP_SELF']) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');
$flash_ok = $_SESSION['flash_ok'] ?? null;
$flash_err = $_SESSION['flash_err'] ?? null;
$flash_toast = $_SESSION['flash_toast'] ?? null;
$old = $_SESSION['old'] ?? [];
$old_edit = $_SESSION['old_edit'] ?? null;
unset($_SESSION['flash_ok'], $_SESSION['flash_err'], $_SESSION['flash_toast'], $_SESSION['old'], $_SESSION['old_edit']);
$urlFiltro = function (string $cat) use ($q): string {
    $params = array_filter(['q' => $q, 'cat' => $cat], fn($v) => $v !== '' && $v !== null);
    return 'productos.php' . ($params ? '?' . http_build_query($params) : '');
};
?>
<?php if($flash_ok): ?>
<div class="alert alert-success d-flex align-items-center gap-2" role="alert" style="border:1px solid #000;border-radius:0;background:#E6F4EA;color:#0a7a42;font-weight:700">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"/></svg>
    <?= htmlspecialchars($flash_ok) ?>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>
<?php if($flash_err): ?>
<div class="alert alert-danger d-flex align-items-center gap-2" role="alert" style="border:1px solid #000;border-radius:0;background:#FFF4DC;color:#8a5a00;font-weight:700">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>
    <?= htmlspecialchars($flash_err) ?>
    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
    <div class="d-flex gap-2 flex-wrap" id="filtrosCat">
        <a class="admin-filter <?= $cat === '' ? 'active' : '' ?>" href="<?= htmlspecialchars($urlFiltro('')) ?>">Todas (<?= (int) $conteoCat['Todas'] ?>)</a>
        <a class="admin-filter <?= $cat === 'Pupusas' ? 'active' : '' ?>" href="<?= htmlspecialchars($urlFiltro('Pupusas')) ?>">Pupusas (<?= (int) $conteoCat['Pupusas'] ?>)</a>
        <a class="admin-filter <?= $cat === 'Bebidas' ? 'active' : '' ?>" href="<?= htmlspecialchars($urlFiltro('Bebidas')) ?>">Bebidas (<?= (int) $conteoCat['Bebidas'] ?>)</a>
        <a class="admin-filter <?= $cat === 'Complementos' ? 'active' : '' ?>" href="<?= htmlspecialchars($urlFiltro('Complementos')) ?>">Complementos (<?= (int) $conteoCat['Complementos'] ?>)</a>
    </div>
    <button class="btn btn-admin btn-admin--black" data-bs-toggle="modal" data-bs-target="#modalCrear">+ Nuevo producto</button>
</div>

<?php include __DIR__ . '/_buscador.php'; ?>

<?php if(empty($productos)): ?>
<div class="admin-panel text-center py-5">
    <div class="mx-auto" style="width:64px;height:64px; border:1px dashed #000; display:flex;align-items:center;justify-content:center; background:#FFF6D3;"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8h15l-1.5 12.5a1 1 0 0 1-1 .5H5.5a1 1 0 0 1-1-.5L3 8h3z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg></div>
    <h3 class="h6 fw-bold mt-3 mb-1"><?= ($q !== '' || $cat !== '') ? 'Ningún producto con este filtro' : 'Sin productos aún' ?></h3>
    <p class="small text-muted mb-3"><?= ($q !== '' || $cat !== '') ? 'Prueba con otra búsqueda o mira todas las categorías.' : 'Crea tu primer producto y aparecerá aquí y en el menú público.' ?></p>
    <?php if ($q !== '' || $cat !== ''): ?>
    <a href="productos.php" class="btn btn-admin btn-admin--white">Ver todos</a>
    <?php else: ?>
    <button class="btn btn-admin btn-admin--black" data-bs-toggle="modal" data-bs-target="#modalCrear">Crear producto</button>
    <p class="small text-muted mt-3 mb-0" style="font-size:0.72rem">Los datos se guardan en <code>MySQL · pupuseria_rinas</code> e imágenes en <code>assets/img/productos/</code></p>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="row g-3" id="gridProductos">
    <?php foreach($productos as $p): 
        $isActivo = $p['estado']==='Activo';
        $catColor = $p['categoria']==='Bebidas' ? '#E8E8F5' : ($p['categoria']==='Complementos' ? '#FFF6D3' : '#FFF4DC');
        $catBorder = $p['categoria']==='Bebidas' ? 'rgba(11,11,69,0.2)' : 'rgba(245,158,11,0.4)';
    ?>
    <div class="col-12 col-sm-6 col-xl-4 producto-card" data-cat="<?= htmlspecialchars($p['categoria']) ?>" data-estado="<?= htmlspecialchars($p['estado']) ?>">
        <div class="admin-panel p-0 overflow-hidden h-100 d-flex flex-column" style="border:1px solid #000">
            <div style="height:160px; background:<?= $p['categoria']==='Pupusas' ? '#fff' : ($p['categoria']==='Bebidas' ? '#FFF6D3' : '#FEF3E2') ?>; display:flex; align-items:center; justify-content:center; border-bottom:1px solid #000; overflow:hidden; position:relative">
                <?php
                    $imgSrc = $p['imagen'];
                    // si es ruta relativa assets/..., prepend ../ para estar en admin/
                    $src = (strpos($imgSrc,'http')===0) ? $imgSrc : '../'.$imgSrc;
                    // si es placeholder con %20, ya codificado
                    $isContain = strpos($imgSrc,'pupa_')!==false || strpos($imgSrc,'productos/')!==false;
                ?>
                <img src="<?= htmlspecialchars($src) ?>" alt="<?= htmlspecialchars($p['nombre']) ?>" style="<?= $isContain ? 'width:100%;height:100%;object-fit:contain;padding:0.5rem' : 'width:92px;height:92px;object-fit:cover;border-radius:50%;border:2px solid var(--rinas-naranja)' ?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                <div style="display:none;width:100%;height:100%;align-items:center;justify-content:center;font-size:3rem">🍽️</div>
                <span class="badge-rinas" style="position:absolute;top:8px;left:8px;background:<?= $catColor ?>;border:1px solid <?= $catBorder ?>;color:#111"><?= htmlspecialchars($p['categoria']) ?></span>
                <span class="badge-rinas" style="position:absolute;top:8px;right:8px;<?= $isActivo ? 'background:#E6F4EA;border-color:rgba(10,122,66,0.25);color:#0a7a42' : 'background:#111;color:#fff;border-color:#111' ?>"><?= $isActivo ? 'Activo' : 'Agotado' ?></span>
            </div>
            <div class="p-3 d-flex flex-column flex-fill">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="small fw-bold" style="letter-spacing:0.04em"><?= $isActivo ? '● Activo' : '○ Agotado' ?></span>
                    <span class="small fw-bold" style="font-size:1.05rem">$<?= number_format(floatval($p['precio']),2) ?></span>
                </div>
                <h3 class="h6 fw-bold mb-1" style="line-height:1.2"><?= htmlspecialchars($p['nombre']) ?></h3>
                <p class="small text-muted mb-3 flex-fill" style="font-size:0.82rem;line-height:1.4"><?= htmlspecialchars($p['descripcion']) ?></p>
                <div class="d-flex gap-2">
                    <button class="btn btn-sm flex-fill btn-editar" data-id="<?= $p['id'] ?>" data-nombre="<?= htmlspecialchars($p['nombre'],ENT_QUOTES) ?>" data-descripcion="<?= htmlspecialchars($p['descripcion'],ENT_QUOTES) ?>" data-precio="<?= $p['precio'] ?>" data-categoria="<?= $p['categoria'] ?>" data-estado="<?= $p['estado'] ?>" data-imagen="<?= htmlspecialchars($p['imagen']) ?>" style="border:1px solid #000; background:#fff; font-weight:700">Editar</button>
                    <a href="productos.php?toggle=<?= $p['id'] ?>&csrf=<?= csrf_token() ?>&back=<?= urlencode($urlActualProd) ?>" class="btn btn-sm flex-fill" style="<?= $isActivo ? 'background:#000;color:#fff;border:1px solid #000' : 'background:var(--rinas-naranja);color:#fff;border:1px solid var(--rinas-naranja)' ?>;font-weight:700"><?= $isActivo ? 'Desactivar' : 'Activar' ?></a>
                </div>
                <a href="productos.php?del=<?= $p['id'] ?>&csrf=<?= csrf_token() ?>&back=<?= urlencode($urlActualProd) ?>" onclick="return confirm('¿Eliminar <?= htmlspecialchars(addslashes($p['nombre'])) ?>?')" class="btn btn-sm w-100 mt-2" style="border:1px solid #000;background:#fff;color:#c0392b;font-weight:700;font-size:0.75rem">Eliminar</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php include __DIR__ . '/_paginacion.php'; ?>
<?php endif; ?>

<!-- Modal Crear -->
<div class="modal fade" id="modalCrear" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:560px">
    <div class="modal-content" style="border:1px solid #000;border-radius:0">
      <div class="modal-header" style="background:var(--rinas-crema);border-bottom:1px solid #000">
        <h5 class="modal-title fw-bold" style="letter-spacing:0.04em;text-transform:uppercase">Nuevo producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" enctype="multipart/form-data" id="formCrear">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="crear">
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em">Nombre *</label>
                    <input name="nombre" value="<?= htmlspecialchars($old['nombre'] ?? '') ?>" class="form-control" style="border:1px solid #000;border-radius:0" placeholder="Ej: Pupusa Revuelta" required minlength="3">
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em">Descripción</label>
                    <textarea name="descripcion" class="form-control" style="border:1px solid #000;border-radius:0" rows="2" placeholder="Chicharrón, frijol y queso..."><?= htmlspecialchars($old['descripcion'] ?? '') ?></textarea>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em">Precio $ *</label>
                    <input name="precio" value="<?= htmlspecialchars($old['precio'] ?? '') ?>" type="number" step="0.01" min="0.01" class="form-control" style="border:1px solid #000;border-radius:0" placeholder="1.50" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em">Categoría *</label>
                    <select name="categoria" class="form-select" style="border:1px solid #000;border-radius:0" required>
                        <option value="Pupusas" <?= (($old['categoria']??'Pupusas')==='Pupusas'?'selected':'') ?>>Pupusas</option>
                        <option value="Bebidas" <?= (($old['categoria']??'')==='Bebidas'?'selected':'') ?>>Bebidas</option>
                        <option value="Complementos" <?= (($old['categoria']??'')==='Complementos'?'selected':'') ?>>Complementos</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em">Estado</label>
                    <!-- El hidden va ANTES que el checkbox: PHP toma el último valor
                         con el mismo name, así desmarcado llega "Agotado". -->
                    <label class="estado-toggle">
                        <input type="hidden" name="estado" value="Agotado">
                        <input type="checkbox" name="estado" value="Activo" id="crear_estado" <?= (($old['estado'] ?? 'Activo') === 'Agotado') ? '' : 'checked' ?>>
                        <span class="estado-toggle-caja" aria-hidden="true"></span>
                        <span class="estado-toggle-texto">Activo</span>
                    </label>
                    <div class="small text-muted mt-1" style="font-size:0.72rem">Desmarcado el producto queda como <strong>Agotado</strong>.</div>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em">Imagen</label>
                    <div class="dropzone-rinas" id="dzCrear"></div>
                    <div class="small text-muted mt-1" style="font-size:0.72rem">Arrastra la foto o haz clic. jpg/png/webp máx 5MB. Si no subes, se usa el placeholder de la categoría.</div>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid #000;background:#fff">
            <button type="button" class="btn btn-admin btn-admin--white" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-admin btn-admin--black">Guardar producto</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Editar -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width:560px">
    <div class="modal-content" style="border:1px solid #000;border-radius:0">
      <div class="modal-header" style="background:#E8E8F5;border-bottom:1px solid #000">
        <h5 class="modal-title fw-bold" style="letter-spacing:0.04em;text-transform:uppercase">Editar producto</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="post" enctype="multipart/form-data">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="editar">
        <input type="hidden" name="id" id="edit_id">
        <div class="modal-body">
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em">Nombre *</label>
                    <input name="nombre" id="edit_nombre" class="form-control" style="border:1px solid #000;border-radius:0" required>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em">Descripción</label>
                    <textarea name="descripcion" id="edit_descripcion" class="form-control" style="border:1px solid #000;border-radius:0" rows="2"></textarea>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em">Precio $ *</label>
                    <input name="precio" id="edit_precio" type="number" step="0.01" min="0.01" class="form-control" style="border:1px solid #000;border-radius:0" required>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em">Categoría</label>
                    <select name="categoria" id="edit_categoria" class="form-select" style="border:1px solid #000;border-radius:0">
                        <option value="Pupusas">Pupusas</option>
                        <option value="Bebidas">Bebidas</option>
                        <option value="Complementos">Complementos</option>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em">Estado</label>
                    <label class="estado-toggle">
                        <input type="hidden" name="estado" value="Agotado">
                        <input type="checkbox" name="estado" value="Activo" id="edit_estado">
                        <span class="estado-toggle-caja" aria-hidden="true"></span>
                        <span class="estado-toggle-texto">Activo</span>
                    </label>
                    <div class="small text-muted mt-1" style="font-size:0.72rem">Desmarcado el producto queda como <strong>Agotado</strong>.</div>
                </div>
                <div class="col-6">
                    <label class="form-label fw-bold small text-uppercase" style="letter-spacing:0.06em">Cambiar imagen</label>
                    <div class="dropzone-rinas" id="dzEditar"></div>
                    <div class="small text-muted mt-1" style="font-size:0.72rem">Si no subes nada, se mantiene la imagen actual.</div>
                </div>
                <div class="col-12">
                    <div id="previewEditarWrap" style="border:1px dashed #000;background:#fff;padding:0.5rem;text-align:center">
                        <img id="imgPreviewEditar" src="" alt="actual" style="max-height:120px;max-width:100%;object-fit:contain">
                        <div class="small text-muted mt-1" style="font-size:0.72rem">Imagen actual</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid #000">
            <button type="button" class="btn btn-admin btn-admin--white" data-bs-dismiss="modal">Cancelar</button>
            <button type="submit" class="btn btn-admin btn-admin--black">Actualizar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="<?= LINK_DROPZONE_JS ?>"></script>
<script>
(function(){
  if (typeof Dropzone === 'undefined') return;

  /* Dropzone NO sube nada por AJAX: solo atiende el arrastre/clic y guarda el
     archivo. Al enviar el formulario lo anexamos al FormData con el nombre
     "imagen", que es lo que el servidor ya lee en $_FILES. Así la validación
     de handleUpload() (mime real, 5MB, extensiones) sigue siendo la de siempre. */
  function crearZona(el) {
    if (!el) return null;
    return new Dropzone(el, {
      url: 'productos.php',        // la API lo exige; nunca se usa
      autoProcessQueue: false,     // el envío lo hace el submit del formulario
      autoQueue: false,
      clickable: true,
      maxFiles: 1,
      maxFilesize: 5,              // MB
      acceptedFiles: 'image/jpeg,image/jpg,image/png,image/webp',
      addRemoveLinks: true,
      timeout: 0
    });
  }

  const dzCrear  = crearZona(document.getElementById('dzCrear'));
  const dzEditar = crearZona(document.getElementById('dzEditar'));

  // Editar modal
  const modalEditar = document.getElementById('modalEditar');
  const bsEditar = modalEditar ? new bootstrap.Modal(modalEditar) : null;
  document.querySelectorAll('.btn-editar').forEach(btn=>{
    btn.addEventListener('click',()=>{
      document.getElementById('edit_id').value = btn.getAttribute('data-id');
      document.getElementById('edit_nombre').value = btn.getAttribute('data-nombre');
      document.getElementById('edit_descripcion').value = btn.getAttribute('data-descripcion');
      document.getElementById('edit_precio').value = btn.getAttribute('data-precio');
      document.getElementById('edit_categoria').value = btn.getAttribute('data-categoria');
      document.getElementById('edit_estado').checked = (btn.getAttribute('data-estado') === 'Activo');
      const img = btn.getAttribute('data-imagen');
      const src = img ? ('../'+img) : '';
      document.getElementById('imgPreviewEditar').src = src;
      // limpiar la zona: no arrastrar el archivo del producto anterior
      if(dzEditar) dzEditar.removeAllFiles();
      if(bsEditar) bsEditar.show();
    });
  });

  /* Envío con FormData para poder anexar el archivo de Dropzone.
     Si no hay imagen no se manda el campo y el servidor aplica el placeholder
     (crear) o conserva la actual (editar): igual que antes. */
  function enviarConImagen(form, zona) {
    if (!form) return;
    let pendiente = false;
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      if (pendiente) return;               // evita doble envío
      pendiente = true;

      const btn = form.querySelector('button[type="submit"]');
      const txt = btn ? btn.innerHTML : '';
      if (btn) { btn.disabled = true; btn.innerHTML = 'Guardando…'; }

      const fd = new FormData(form);
      const archivo = zona && zona.files.length ? zona.files[0] : null;
      if (archivo) fd.set('imagen', archivo, archivo.name);

      fetch(form.getAttribute('action') || window.location.href, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      })
        .then(r => (r.url && r.redirected) ? r.url : window.location.href)
        .then(url => { window.location.href = url; })
        .catch(() => {
          pendiente = false;
          if (btn) { btn.disabled = false; btn.innerHTML = txt; }
        });
    });
  }

  enviarConImagen(document.getElementById('formCrear'), dzCrear);
  enviarConImagen(document.querySelector('#modalEditar form'), dzEditar);

  // Auto-open crear si hubo error de validación
  <?php if($flash_err && !empty($old)): ?> 
    const mCrear = document.getElementById('modalCrear');
    if(mCrear) new bootstrap.Modal(mCrear).show();
  <?php endif; ?>
  // Auto-open editar si hubo error al editar
  <?php if($flash_err && !empty($old_edit)): ?>
    document.getElementById('edit_id').value = "<?= htmlspecialchars($old_edit['id'] ?? '', ENT_QUOTES) ?>";
    document.getElementById('edit_nombre').value = "<?= htmlspecialchars($old_edit['nombre'] ?? '', ENT_QUOTES) ?>";
    document.getElementById('edit_descripcion').value = "<?= htmlspecialchars($old_edit['descripcion'] ?? '', ENT_QUOTES) ?>";
    document.getElementById('edit_precio').value = "<?= htmlspecialchars($old_edit['precio'] ?? '', ENT_QUOTES) ?>";
    document.getElementById('edit_categoria').value = "<?= htmlspecialchars($old_edit['categoria'] ?? '', ENT_QUOTES) ?>";
    document.getElementById('edit_estado').checked = ("<?= htmlspecialchars($old_edit['estado'] ?? '', ENT_QUOTES) ?>" === 'Activo');
    // mantener imagen previa si existe
    <?php
      $eid = intval($old_edit['id'] ?? 0);
      $prevImg = '';
      foreach(($productos ?? []) as $pp) if($pp['id']==$eid) { $prevImg=$pp['imagen']; break; }
    ?>
    document.getElementById('imgPreviewEditar').src = "<?= $prevImg ? '../'.$prevImg : '' ?>";
    if(bsEditar) bsEditar.show();
  <?php endif; ?>
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
