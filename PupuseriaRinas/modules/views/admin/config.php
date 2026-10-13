<?php
// $config, $hayConfig, $flash_ok, $flash_err vienen de admin/config.php
$config = $config ?? [];
foreach (['nombre', 'whatsapp', 'telefono', 'direccion', 'maps_query', 'horario_texto', 'instagram', 'facebook', 'logo'] as $claveCfg) {
    $config[$claveCfg] = (string) ($config[$claveCfg] ?? '');
}
$hayConfig = $hayConfig ?? false;
$flash_toast = $flash_toast ?? null;
$flash_err = $flash_err ?? null;
$waLink = $config['whatsapp'] !== '' ? 'https://wa.me/' . $config['whatsapp'] : null;
$mapaQ = $config['maps_query'] !== '' ? $config['maps_query'] : ($config['direccion'] !== '' ? $config['direccion'] : 'Pupusería Rinas, El Salvador');
?>
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
                <div class="d-flex align-items-center gap-3 flex-wrap">
                    <div class="admin-logo-preview" id="logoPreviewCaja">
                        <img id="logoPreviewImg" src="<?= $config['logo'] !== '' ? '../' . htmlspecialchars($config['logo'], ENT_QUOTES) : '' ?>" alt="Logo actual"<?= $config['logo'] !== '' ? '' : ' hidden' ?>>
                        <span class="small text-muted" id="logoSinTexto"<?= $config['logo'] !== '' ? ' hidden' : '' ?>>Sin logo</span>
                    </div>

                    <!-- El input nativo queda oculto: Dropzone solo se encarga de
                         la interfaz (arrastrar / clic) y copia el archivo elegido
                         aquí, así el servidor sigue leyendo $_FILES['logo'] igual. -->
                    <input id="cfgLogo" name="logo" type="file" accept="image/jpeg,image/png,image/webp,image/svg+xml" hidden>
                    <div class="dropzone-rinas dropzone-rinas--logo" id="dzLogo">
                        <div class="dz-message">
                            <span class="dz-icon" aria-hidden="true"><i class="fa-solid fa-cloud-arrow-up"></i></span>
                            Arrastra el logo aquí o haz clic para elegirlo
                        </div>
                    </div>
                </div>
                <div class="small text-muted mt-1" style="font-size:0.72rem">Arrastra la imagen o haz clic. jpg/png/webp/svg máx 2MB.</div>
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
                    <button type="button" id="btnEstoyAqui" class="btn btn-sm btn-admin btn-admin--black w-100 mb-2" style="border:1px solid #000">
                        <i class="fa-solid fa-location-crosshairs me-1"></i>Estoy aquí
                    </button>
                    <div id="ubicacionEstado" class="small mb-2" style="font-size:0.72rem;display:none;border:1px solid #000;padding:0.35rem 0.5rem"></div>
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
<script>
(function () {
  var btn = document.getElementById('btnEstoyAqui');
  var estado = document.getElementById('ubicacionEstado');
  var inputMap = document.getElementById('cfgMap');
  var iframe = document.querySelector('iframe');
  var csrfEl = document.querySelector('input[name="csrf"]');
  if (!btn) return;

  function mostrar(tipo, texto) {
    if (!estado) return;
    estado.style.display = 'block';
    estado.textContent = texto;
    if (tipo === 'ok')        { estado.style.background = '#E6F4EA'; estado.style.color = '#0a7a42'; }
    else if (tipo === 'carga'){ estado.style.background = '#FFF6D3'; estado.style.color = '#8a5a00'; }
    else                      { estado.style.background = '#FFF4DC'; estado.style.color = '#c0392b'; }
  }

  function mensajeError(code) {
    if (code === 1) return 'Permiso de ubicación denegado. Actívalo en el navegador.';
    if (code === 2) return 'No se pudo obtener tu ubicación (señal o GPS apagado).';
    if (code === 3) return 'Tiempo agotado esperando la ubicación. Intenta de nuevo.';
    return 'No se pudo obtener la ubicación.';
  }

  btn.addEventListener('click', function () {
    if (!navigator.geolocation) {
      mostrar('err', 'Tu navegador no soporta geolocalización.');
      return;
    }
    btn.disabled = true;
    mostrar('carga', 'Obteniendo ubicación…');

    navigator.geolocation.getCurrentPosition(function (pos) {
      var lat = pos.coords.latitude.toFixed(6);
      var lng = pos.coords.longitude.toFixed(6);
      var coords = lat + ',' + lng;

      // 1) Actualiza el campo y el preview del mapa al instante
      if (inputMap) inputMap.value = coords;
      if (iframe) iframe.src = 'https://maps.google.com/maps?q=' + encodeURIComponent(coords) + '&z=16&output=embed';

      // 2) Guarda la ubicación en la web (maps_query) sin recargar
      fetch('config.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          action: 'guardar_ubicacion',
          csrf: csrfEl ? csrfEl.value : '',
          maps_query: coords
        }).toString(),
        credentials: 'same-origin'
      })
        .then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
        .then(function (data) {
          if (data && data.ok) {
            mostrar('ok', 'Ubicación guardada: ' + lat + ', ' + lng);
          } else {
            mostrar('err', (data && data.error) ? data.error : 'No se pudo guardar la ubicación.');
          }
        })
        .catch(function () { mostrar('err', 'Error de conexión al guardar.'); })
        .finally(function () { btn.disabled = false; });
    }, function (err) {
      mostrar('err', mensajeError(err.code));
      btn.disabled = false;
    }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 });
  });
})();
</script>

<script src="<?= LINK_DROPZONE_JS ?>"></script>
<script>
/* ============================================================================
   Logo: Dropzone como intercara (arrastrar o clic)
   ----------------------------------------------------------------------------
   Dropzone NO sube nada por AJAX aqui: solo se encarga de la interfaz. El
   archivo elegido se copia al <input type="file"> oculto con un DataTransfer,
   asi el servidor sigue leyendo $_FILES['logo'] y la validación de
   admin/config.php (mime real, 2MB, extensiones) no cambia.
   ============================================================================ */
(function () {
  if (typeof Dropzone === 'undefined') return;
  Dropzone.autoDiscover = false;

  var input     = document.getElementById('cfgLogo');
  var img       = document.getElementById('logoPreviewImg');
  var sinLogo   = document.getElementById('logoSinTexto');
  var guardado  = img ? (img.getAttribute('src') || '') : '';
  var urlPrevia = '';

  /* El circulo de la izquierda adelanta lo que se va a guardar.
     src: la url buena (blob del archivo nuevo o la ruta ya guardada). */
  function pintar(src) {
    if (!img || !sinLogo) return;
    var previo = urlPrevia;
    if (src) { img.src = src; img.hidden = false; sinLogo.hidden = true; }
    else     { img.hidden = true; sinLogo.hidden = false; }
    urlPrevia = (src && src.indexOf('blob:') === 0) ? src : '';
    /* Se revoca la url anterior DESPUES de apuntar al <img> a la buena:
       revocar la que se acaba de asignar deja la imagen del circulo rota
       (ERR_FILE_NOT_FOUND en consola). */
    if (previo && previo !== urlPrevia) URL.revokeObjectURL(previo);
  }

  /* DataTransfer es la unica forma de asignar archivos a un input desde JS */
  function sincronizar(files) {
    if (!input) return;
    var dt = new DataTransfer();
    if (files && files.length) dt.items.add(files[0]);
    input.files = dt.files;
  }

  var zona = new Dropzone('#dzLogo', {
    url: 'config.php',            // la API de Dropzone lo exige; no se usa
    autoProcessQueue: false,      // el envio lo hace el submit del formulario
    autoQueue: false,
    clickable: true,
    maxFiles: 1,
    maxFilesize: 2,               // MB
    acceptedFiles: 'image/jpeg,image/jpg,image/png,image/webp,image/svg+xml',
    addRemoveLinks: true,
    timeout: 0,
    dictDefaultMessage: 'Arrastra el logo aquí o haz clic para elegirlo',
    dictRemoveFile: 'Quitar',
    dictFileTooBig: 'El archivo supera los 2 MB.',
    dictInvalidFileType: 'Formato no permitido (jpg/png/webp/svg).',
    dictMaxFilesExceeded: 'Solo se permite un logo.'
  });

  /* Mismos limites que el servidor, aqui en el navegador: asi un archivo
     invalido se descarta sin llevarse por delante el que ya estaba elegido. */
  function valido(file) {
    var tipos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/svg+xml'];
    return !!file && file.size <= 2 * 1024 * 1024 && tipos.indexOf(file.type) >= 0;
  }

  zona.on('addedfile', function (file) {
    if (!valido(file)) { zona.removeFile(file); return; }
    /* El ultimo archivo elegido manda: si ya habia uno, se descarta */
    while (zona.files.length > 1) zona.removeFile(zona.files[0]);
    sincronizar(zona.files);
    if (file.type && file.type.indexOf('image/') === 0) {
      pintar(URL.createObjectURL(file));
    }
  });

  zona.on('removedfile', function () {
    sincronizar(zona.files);
    if (!zona.files.length) pintar(guardado);
  });

  /* Dropzone agrega el archivo y lo marca en rojo cuando no pasa la
     validación (formato o tamaño). Se quita para que un archivo inválido
     nunca llegue al formulario: el servidor lo rechazaría de todos modos. */
  zona.on('error', function (file) {
    if (file && file.accepted === false && zona.files.indexOf(file) >= 0) {
      zona.removeFile(file);
    }
  });
})();
</script>

<?php if (!empty($flash_toast)): ?>
<script src="<?= LINK_SWEETALERT_JS ?>"></script>
<script>
/* Aviso de "Configuración guardada" como toast arriba a la derecha, igual que
   en productos y pedidos: no interrumpe y se va solo a los 3 segundos. */
(function () {
  if (typeof Swal === 'undefined') return;
  const t = <?= json_encode(is_array($flash_toast) ? $flash_toast : ['icon' => 'info', 'title' => '', 'text' => ''], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
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
