<?php
/**
 * Vista del POS. Variables del controlador admin/pos.php:
 *   $productos, $categorias, $abiertos, $negocio, $logo, $estados, $siguiente, $flash_ok, $flash_err
 */
$productos  = $productos  ?? [];
$categorias = $categorias ?? [];
$abiertos   = $abiertos   ?? [];
$estados    = $estados    ?? ['pendiente', 'cocina', 'listo', 'entregado'];
$siguiente  = $siguiente  ?? ['pendiente' => 'cocina', 'cocina' => 'listo', 'listo' => 'entregado'];
$flash_ok   = $flash_ok   ?? null;
$flash_err  = $flash_err  ?? null;

$etiquetaEstado = ['pendiente' => 'Pendiente', 'cocina' => 'En cocina', 'listo' => 'Listo', 'entregado' => 'Entregado'];
$etiquetaTipo   = ['recoger' => 'Recoger', 'llevar' => 'Llevar'];
$conteoAbiertos = $conteoAbiertos ?? ['pendiente' => 0, 'cocina' => 0, 'listo' => 0];
$totalAbiertos  = array_sum($conteoAbiertos);
$imgPorDefecto  = [
    'Bebidas'     => 'assets/img/especialidad_loroco.png',
    'Complementos' => 'assets/img/images - Editado.png',
    'Pupusas'     => 'assets/img/pupa_camaron.png',
];

// Rangos de precio del filtro (solo los que existen en el catálogo)
$rangoDe = function (float $precio): string {
    if ($precio < 2)     { return 'a2'; }
    if ($precio < 5)     { return '2a5'; }
    if ($precio < 10)    { return '5a10'; }
    return '10mas';
};

$usados = [];
foreach ($productos as $p) {
    $usados[$rangoDe((float) $p['precio'])] = true;
}
$etiquetaRango = ['a2' => 'Hasta $2', '2a5' => '$2 a $5', '5a10' => '$5 a $10', '10mas' => 'Más de $10'];
$rangos = array_values(array_filter(array_keys($etiquetaRango), fn($r) => isset($usados[$r])));
?>
<div class="pos-wrap">

  <?php if ($flash_ok): ?>
  <div class="pos-flash pos-flash--ok" role="status">
    <i class="fa-solid fa-circle-check" aria-hidden="true"></i> <?= htmlspecialchars($flash_ok) ?>
  </div>
  <?php endif; ?>

  <?php if ($flash_err): ?>
  <div class="pos-flash pos-flash--err" role="alert">
    <i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i> <?= htmlspecialchars($flash_err) ?>
  </div>
  <?php endif; ?>

  <div class="pos-grid">

    <!-- ============ IZQUIERDA: filtros + catalogo ============ -->
    <section class="pos-catalogo">
      <div class="pos-filtros">
        <div class="pos-filtro pos-filtro--buscar">
          <label for="posBuscar">Buscar</label>
          <div class="pos-input-wrap">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <input type="search" id="posBuscar" placeholder="Nombre del producto&hellip;" autocomplete="off">
          </div>
        </div>

        <div class="pos-filtro">
          <label for="posCategoria">Categor&iacute;a</label>
          <select id="posCategoria">
            <option value="">Todas</option>
            <?php foreach ($categorias as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="pos-filtro">
          <label for="posPrecio">Precio</label>
          <select id="posPrecio">
            <option value="">Todos</option>
            <?php foreach ($rangos as $r): ?>
            <option value="<?= $r ?>"><?= $etiquetaRango[$r] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <p class="pos-conteo" id="posConteo" aria-live="polite"></p>

      <div class="pos-productos" id="posProductos">
        <?php if (!$productos): ?>
        <p class="pos-vacio">No hay productos activos. Agrégalos desde el panel.</p>
        <?php endif; ?>

        <?php foreach ($productos as $p): ?>
          <?php
            $precio = (float) $p['precio'];
            $rango  = $rangoDe($precio);
            $catCol = $p['categoria'] === 'Bebidas' ? '#E8E8F5'
                    : ($p['categoria'] === 'Complementos' ? '#FFF6D3' : '#FFF4DC');
            $imgSrc = (strpos((string) $p['imagen'], 'http') === 0)
                ? (string) $p['imagen']
                : '../' . (string) ($p['imagen'] ?: ($imgPorDefecto[$p['categoria']] ?? 'assets/img/pupa_camaron.png'));
          ?>
        <button type="button" class="pos-card"
                data-id="<?= (int) $p['id'] ?>"
                data-nombre="<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>"
                data-precio="<?= $precio ?>"
                data-categoria="<?= htmlspecialchars($p['categoria']) ?>"
                data-rango="<?= $rango ?>"
                style="--cat-color:<?= $catCol ?>">
          <span class="pos-card-img">
            <img src="<?= htmlspecialchars($imgSrc) ?>" alt="" loading="lazy" onerror="this.remove()">
          </span>
          <span class="pos-card-nombre"><?= htmlspecialchars($p['nombre']) ?></span>
          <span class="pos-card-precio">$<?= number_format($precio, 2) ?></span>
          <span class="pos-card-cat"><?= htmlspecialchars($p['categoria']) ?></span>
        </button>
        <?php endforeach; ?>
      </div>

      <p class="pos-vacio" id="posSinResultados" hidden>Ningún producto coincide con el filtro.</p>
    </section>

    <!-- ============ DERECHA: pedido del cliente ============ -->
    <aside class="pos-panel">
      <div class="pos-panel-head">
        <?php if (!empty($logo)): ?>
        <img src="<?= htmlspecialchars($logo) ?>" alt="" class="pos-negocio-logo">
        <?php endif; ?>
        <div class="pos-negocio-datos">
          <h2 class="pos-negocio-nombre"><?= htmlspecialchars($negocio ?? 'Pupusería Rinas') ?></h2>
          <span class="pos-negocio-sub">Pedido en mostrador</span>
        </div>

        <!-- Botón de pedidos en curso: abre el panel con su estado -->
        <button type="button" class="pos-btn-cursos" data-bs-toggle="modal" data-bs-target="#modalEstados"
                aria-label="Ver estado de los pedidos pendientes">
          <i class="fa-solid fa-receipt" aria-hidden="true"></i>
          <span class="pos-btn-cursos-texto">Pedidos</span>
          <span class="pos-cursos-badge<?= $totalAbiertos > 0 ? '' : ' pos-cursos-badge--vacio' ?>">
            <?= (int) $totalAbiertos ?>
          </span>
        </button>

        <!-- Pantalla completa: oculta el menú lateral para dejar el POS a su aire.
             El icono real lo pone el script según la preferencia guardada. -->
        <button type="button" class="pos-btn-full" id="posBtnFull"
                aria-pressed="false"
                title="Pantalla completa (ocultar menú)"
                aria-label="Pantalla completa: ocultar el menú lateral">
          <i class="fa-solid fa-expand" aria-hidden="true"></i>
        </button>
      </div>

      <form method="post" id="posForm" class="pos-form">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="cobrar">

        <section class="pos-bloque">
          <h3 class="pos-bloque-titulo">Datos del cliente</h3>

          <div class="pos-tipos" role="group" aria-label="Tipo de pedido">
            <label class="pos-tipo">
              <input type="radio" name="tipo" value="recoger" checked>
              <span class="pos-tipo-fondo">
                <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                <span class="pos-tipo-texto">Recoger</span>
              </span>
            </label>
            <label class="pos-tipo">
              <input type="radio" name="tipo" value="llevar">
              <span class="pos-tipo-fondo">
                <i class="fa-solid fa-motorcycle" aria-hidden="true"></i>
                <span class="pos-tipo-texto">Llevar</span>
              </span>
            </label>
          </div>

          <div class="pos-campos">
            <label class="pos-campo">
              <i class="fa-solid fa-user" aria-hidden="true"></i>
              <input type="text" name="cliente" placeholder="Nombre del cliente" maxlength="100" required>
            </label>
            <label class="pos-campo">
              <i class="fa-solid fa-phone" aria-hidden="true"></i>
              <input type="tel" name="telefono" placeholder="Teléfono (opcional)" maxlength="20">
            </label>
          </div>
        </section>

        <section class="pos-bloque pos-bloque--carrito">
          <div class="pos-bloque-cab">
            <h3 class="pos-bloque-titulo">Productos que lleva el cliente</h3>
            <span class="pos-contador" id="posCarritoCount" hidden>0</span>
          </div>

          <div class="pos-carrito" id="posCarrito">
            <p class="pos-carrito-vacio" id="posCarritoVacio">
              <i class="fa-solid fa-basket-shopping" aria-hidden="true"></i>
              Todavía no hay productos.<br>Toca una tarjeta para agregarla.
            </p>
            <div class="pos-lineas" id="posLineas"></div>
          </div>
        </section>

        <div class="pos-total">
          <span class="pos-total-label">Total</span>
          <output class="pos-total-valor" id="posTotal" for="posForm">$0.00</output>
        </div>

        <p class="pos-error" id="posError" role="alert" hidden></p>

        <button type="submit" class="pos-cobrar" id="posCobrar">
          <i class="fa-sharp fa-solid fa-money-check-dollar" aria-hidden="true"></i>
          <span>Cobrar</span>
        </button>
      </form>
    </aside>
  </div>

  <!-- ============ Panel de estados de los pedidos en curso ============ -->
  <div class="modal fade" id="modalEstados" tabindex="-1" aria-labelledby="modalEstadosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width:640px">
      <div class="modal-content" style="border:1px solid #000;border-radius:0">
        <div class="modal-header" style="background:var(--rinas-azul);border-bottom:3px solid var(--rinas-naranja);color:#fff">
          <h5 class="modal-title" id="modalEstadosLabel" style="font-size:0.95rem;font-weight:800;letter-spacing:0.05em;text-transform:uppercase">
            Pedidos en curso
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>

        <div class="modal-body">
          <?php if (!$abiertos): ?>
            <p class="pos-modal-vacio">
              <i class="fa-solid fa-bell-concierge" aria-hidden="true"></i>
              No hay pedidos pendientes. Los que cobres aparecerán aquí.
            </p>
          <?php else: ?>
            <!-- Resumen por estado -->
            <div class="pos-modal-resumen">
              <span class="pos-resumen-chip pos-resumen-chip--pendiente"><?= (int) $conteoAbiertos['pendiente'] ?> pendiente<?= $conteoAbiertos['pendiente'] == 1 ? '' : 's' ?></span>
              <span class="pos-resumen-chip pos-resumen-chip--cocina"><?= (int) $conteoAbiertos['cocina'] ?> en cocina</span>
              <span class="pos-resumen-chip pos-resumen-chip--listo"><?= (int) $conteoAbiertos['listo'] ?> listo<?= $conteoAbiertos['listo'] == 1 ? '' : 's' ?></span>
            </div>

            <div class="pos-modal-lista">
              <?php foreach ($abiertos as $o): $sig = $siguiente[$o['estado']] ?? null; ?>
              <article class="pos-pedido pos-pedido--<?= htmlspecialchars($o['estado']) ?>">
                <div class="pos-pedido-cab">
                  <strong class="pos-pedido-folio"><?= htmlspecialchars($o['folio'] ?: '#' . (int) $o['id']) ?></strong>
                  <span class="pos-estado pos-estado--<?= htmlspecialchars($o['estado']) ?>">
                    <?= $etiquetaEstado[$o['estado']] ?? htmlspecialchars($o['estado']) ?>
                  </span>
                  <span class="pos-pedido-hora"><?= date('d/m H:i', strtotime($o['creado'])) ?></span>
                </div>
                <div class="pos-pedido-cliente">
                  <?= htmlspecialchars($o['cliente']) ?><?= $o['telefono'] ? ' · ' . htmlspecialchars($o['telefono']) : '' ?>
                </div>
                <div class="pos-pedido-detalle"><?= htmlspecialchars($o['detalle']) ?></div>
                <?php if (!empty($o['notas'])): ?>
                  <div class="pos-pedido-nota">
                    <i class="fa-solid fa-note-sticky" aria-hidden="true"></i> <?= htmlspecialchars($o['notas']) ?>
                  </div>
                <?php endif; ?>
                <div class="pos-pedido-pie">
                  <span class="pos-pedido-total">$<?= number_format((float) $o['total'], 2) ?></span>
                  <span class="pos-pedido-tipo"><?= $etiquetaTipo[$o['tipo']] ?? htmlspecialchars($o['tipo']) ?></span>
                  <form method="post" class="pos-pedido-acciones">
                    <?= csrf_input() ?>
                    <input type="hidden" name="action" value="estado">
                    <input type="hidden" name="id" value="<?= (int) $o['id'] ?>">
                    <button type="submit" name="estado" value="<?= $sig ?? 'entregado' ?>"
                            class="pos-btn-estado<?= $sig ? '' : ' pos-btn-estado--ok' ?>">
                      <?= $sig ? htmlspecialchars($etiquetaEstado[$sig]) : 'Entregar' ?>
                    </button>
                  </form>
                </div>
              </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>

        <div class="modal-footer" style="border-top:1px solid #e9e6d8">
          <a href="pedidos.php" class="btn btn-sm btn-admin" style="border:1px solid #000;background:#fff">Ver todos los pedidos →</a>
          <button type="button" class="btn btn-sm btn-admin btn-admin--black" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  'use strict';

  /* ---------- Catalogo: filtrar por nombre, categoria y precio ---------- */
  var tarjetas  = Array.prototype.slice.call(document.querySelectorAll('.pos-card'));
  var inpBuscar = document.getElementById('posBuscar');
  var selCat    = document.getElementById('posCategoria');
  var selPrecio = document.getElementById('posPrecio');
  var elConteo  = document.getElementById('posConteo');
  var elVacio   = document.getElementById('posSinResultados');
  var elGrid    = document.getElementById('posProductos');

  // quita acentos para que "pupusa" encuentre "Pupusa"
  var normalizar = function (s) {
    return s.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
  };

  function filtrar() {
    var texto = normalizar(inpBuscar.value.trim());
    var cat = selCat.value;
    var rango = selPrecio.value;
    var visibles = 0;

    tarjetas.forEach(function (t) {
      var coincide =
        (!texto || normalizar(t.dataset.nombre).indexOf(texto) !== -1) &&
        (!cat   || t.dataset.categoria === cat) &&
        (!rango || t.dataset.rango === rango);
      t.hidden = !coincide;
      if (coincide) visibles++;
    });

    elConteo.textContent = visibles === tarjetas.length
      ? visibles + (visibles === 1 ? ' producto' : ' productos')
      : visibles + ' de ' + tarjetas.length + ' productos';
    elVacio.hidden = visibles > 0;
  }

  inpBuscar.addEventListener('input', filtrar);
  selCat.addEventListener('change', filtrar);
  selPrecio.addEventListener('change', filtrar);
  if (tarjetas.length) filtrar();

  /* ---------- Carrito ---------- */
  var carrito  = new Map();
  var elLineas = document.getElementById('posLineas');
  var elVacioC = document.getElementById('posCarritoVacio');
  var elContador = document.getElementById('posCarritoCount');
  var elTotal  = document.getElementById('posTotal');
  var elError  = document.getElementById('posError');
  var form     = document.getElementById('posForm');

  var money = function (n) { return '$' + n.toFixed(2); };

  function agregar(tarjeta) {
    var id = tarjeta.dataset.id;
    var actual = carrito.get(id);
    if (actual) {
      actual.cantidad++;
    } else {
      carrito.set(id, {
        id: id,
        nombre: tarjeta.dataset.nombre,
        precio: parseFloat(tarjeta.dataset.precio) || 0,
        cantidad: 1
      });
    }
    tarjeta.classList.remove('pos-card--sumado');
    void tarjeta.offsetWidth;                 // reinicia la animacion
    tarjeta.classList.add('pos-card--sumado');
    pintar();
  }

  function cambiar(id, delta) {
    var linea = carrito.get(id);
    if (!linea) return;
    linea.cantidad += delta;
    if (linea.cantidad < 1) carrito.delete(id);
    pintar();
  }

  function pintar() {
    var lineas = Array.from(carrito.values());
    elLineas.innerHTML = '';

    var articulos = 0;
    lineas.forEach(function (l) {
      articulos += l.cantidad;
      var div = document.createElement('div');
      div.className = 'pos-linea';
      div.dataset.id = l.id;
      div.innerHTML =
        '<div class="pos-linea-info">' +
          '<span class="pos-linea-nombre"></span>' +
          '<span class="pos-linea-unit"></span>' +
        '</div>' +
        '<div class="pos-linea-ctrl">' +
          '<button type="button" class="pos-cant-btn" data-accion="menos" aria-label="Quitar uno">&minus;</button>' +
          '<span class="pos-cant-val">' + l.cantidad + '</span>' +
          '<button type="button" class="pos-cant-btn" data-accion="mas" aria-label="Agregar uno">+</button>' +
        '</div>' +
        '<span class="pos-linea-total"></span>' +
        '<button type="button" class="pos-quitar-btn" data-accion="quitar" aria-label="Quitar producto">&times;</button>' +
        '<input type="hidden" name="linea_id[]" value="' + l.id + '">' +
        '<input type="hidden" name="linea_cant[]" value="' + l.cantidad + '">';
      // los textos van como texto, nunca como HTML
      div.querySelector('.pos-linea-nombre').textContent = l.nombre;
      div.querySelector('.pos-linea-unit').textContent = money(l.precio) + ' c/u';
      div.querySelector('.pos-linea-total').textContent = money(l.precio * l.cantidad);
      elLineas.appendChild(div);
    });

    var vacio = lineas.length === 0;
    elVacioC.hidden = !vacio;
    elContador.hidden = vacio;
    elContador.textContent = articulos;
    elTotal.textContent = money(lineas.reduce(function (s, l) { return s + l.precio * l.cantidad; }, 0));
  }

  // Click en una tarjeta = agregar
  elGrid.addEventListener('click', function (e) {
    var card = e.target.closest('.pos-card');
    if (card && !card.hidden) agregar(card);
  });

  // Controles del carrito
  elLineas.addEventListener('click', function (e) {
    var btn = e.target.closest('button[data-accion]');
    if (!btn) return;
    var id = btn.closest('.pos-linea').dataset.id;
    var accion = btn.dataset.accion;
    if (accion === 'mas') cambiar(id, 1);
    else if (accion === 'menos') cambiar(id, -1);
    else if (accion === 'quitar') { carrito.delete(id); pintar(); }
  });

  /* ---------- Cobrar ---------- */
  var enviando = false;
  form.addEventListener('submit', function (e) {
    if (!carrito.size) {
      e.preventDefault();
      elError.textContent = 'Agrega al menos un producto.';
      elError.hidden = false;
      return;
    }
    if (enviando) { e.preventDefault(); return; }
    enviando = true;
    elError.hidden = true;
    var btn = document.getElementById('posCobrar');
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Cobrando…';
  });

  pintar();

  /* ---------- Pantalla completa: ocultar el menú lateral ---------- */
  /* El <head> ya aplicó la preferencia guardada; aquí solo reflejarla en el
     botón y poder alternarla. Sin esto el icono mentiria al recargar. */
  var btnFull = document.getElementById('posBtnFull');
  var raiz    = document.documentElement;
  var LLAVE   = 'rinas_pos_fullscreen';

  function pintarBotonFull(activo) {
    if (!btnFull) return;
    btnFull.setAttribute('aria-pressed', activo ? 'true' : 'false');
    btnFull.title = activo ? 'Salir de pantalla completa' : 'Pantalla completa (ocultar menú)';
    btnFull.setAttribute('aria-label', activo
      ? 'Salir de pantalla completa'
      : 'Pantalla completa: ocultar el menú lateral');
    var icono = btnFull.querySelector('i');
    if (icono) icono.className = 'fa-solid ' + (activo ? 'fa-compress' : 'fa-expand');
  }

  function guardarFull(activo) {
    try { localStorage.setItem(LLAVE, activo ? '1' : '0'); } catch (e) { /* sin storage */ }
  }

  function ponerFull(activo) {
    raiz.classList.toggle('pos-fullscreen', activo);
    pintarBotonFull(activo);
    guardarFull(activo);
  }

  if (btnFull) {
    pintarBotonFull(raiz.classList.contains('pos-fullscreen'));
    btnFull.addEventListener('click', function () {
      ponerFull(!raiz.classList.contains('pos-fullscreen'));
    });
  }

  // ESC devuelve el menú, que si no el POS queda sin salida visible
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape') return;
    var modalAbierto = document.querySelector('.modal.show');
    if (modalAbierto) return;                 // ESC cierra primero el modal
    if (raiz.classList.contains('pos-fullscreen')) {
      ponerFull(false);
      if (btnFull) btnFull.focus();
    }
  });

  /* ---------- Panel de estados: reabrir tras avanzar un pedido ---------- */
  var elModal = document.getElementById('modalEstados');
  <?php if (!empty($abrirPanel) && $abiertos): ?>
    if (elModal && typeof bootstrap !== 'undefined') {
      new bootstrap.Modal(elModal).show();
    }
  <?php endif; ?>
})();
</script>
