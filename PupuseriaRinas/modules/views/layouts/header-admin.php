<div class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="admin-burger" type="button" id="adminBurger" aria-label="Abrir menú" aria-controls="adminSidebar" aria-expanded="false">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16"/><path d="M4 12h16"/><path d="M4 18h16"/></svg>
        </button>
        <div>
            <h1 class="admin-topbar-title"><?php echo $admin_title ?? 'Panel'; ?></h1>
            <?php if(!empty($admin_sub)): ?><p class="admin-topbar-sub"><?php echo $admin_sub; ?></p><?php endif; ?>
        </div>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="../index.php" class="btn btn-sm" style="border:1px solid #000; background:#fff; font-weight:700; font-size:0.78rem;">Ver web</a>
        <span class="badge-rinas badge-entregado"><?= htmlspecialchars(strtoupper(usuario_actual()['rol'] ?? 'admin')) ?></span>
    </div>
</div>
<script>
(function () {
  var burger    = document.getElementById('adminBurger');
  var sidebar   = document.getElementById('adminSidebar');
  var overlay   = document.getElementById('adminOverlay');
  var toggleBtn = document.getElementById('adminSidebarClose');
  if (!burger || !sidebar || !overlay) return;

  var raiz  = document.documentElement;
  var movil = window.matchMedia('(max-width: 991.98px)');
  var LLAVE = 'rinas_admin_sidebar';

  /* ---------- Preferencia persistente (escritorio) ---------- */

  function leerPreferencia() {
    try { return localStorage.getItem(LLAVE) === 'rail' ? 'rail' : 'expandido'; }
    catch (e) { return 'expandido'; }
  }

  function guardarPreferencia(estado) {
    try { localStorage.setItem(LLAVE, estado); } catch (e) { /* sin storage: solo sesión */ }
  }

  function aplicarRail(rail) {
    raiz.classList.toggle('admin-menu-cerrado', rail);
  }

  /* ---------- Estado visible ---------- */

  function estaVisible() {
    return movil.matches ? sidebar.classList.contains('show') : !raiz.classList.contains('admin-menu-cerrado');
  }

  function sincronizarAria() {
    var visible = estaVisible();
    var etiqueta = visible ? 'Colapsar menú' : 'Expandir menú';
    burger.setAttribute('aria-expanded', visible ? 'true' : 'false');
    burger.setAttribute('aria-label', etiqueta);
    if (toggleBtn) {
      toggleBtn.setAttribute('aria-label', etiqueta);
      toggleBtn.setAttribute('title', etiqueta);
    }
  }

  function abrir() {
    if (movil.matches) {
      sidebar.classList.add('show');
      overlay.classList.add('show');
      document.body.classList.add('admin-menu-abierto');
    } else {
      aplicarRail(false);
      guardarPreferencia('expandido');
    }
    sincronizarAria();
    var foco = sidebar.querySelector('.admin-nav-link.active') || sidebar.querySelector('.admin-nav-link');
    if (foco) foco.focus();
  }

  function cerrar() {
    if (movil.matches) {
      sidebar.classList.remove('show');
      overlay.classList.remove('show');
      document.body.classList.remove('admin-menu-abierto');
    } else {
      // En escritorio no desaparece del todo: queda el rail de solo iconos
      aplicarRail(true);
      guardarPreferencia('rail');
    }
    sincronizarAria();
  }

  function alternar() {
    estaVisible() ? cerrar() : abrir();
  }

  // El burger del topbar (solo móvil) y la flecha del sidebar hacen lo mismo
  burger.addEventListener('click', alternar);
  if (toggleBtn) toggleBtn.addEventListener('click', alternar);

  // Cierra al pulsar fuera, al pulsar un enlace en móvil o con la tecla ESC
  overlay.addEventListener('click', cerrar);
  sidebar.addEventListener('click', function (e) {
    // En escritorio un clic en un enlace navega: el rail se conserva
    if (movil.matches && e.target.closest('.admin-nav-link')) cerrar();
  });
  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape' || !estaVisible()) return;
    cerrar();
    var destino = movil.matches ? burger : (toggleBtn || burger);
    if (destino) destino.focus();
  });

  /* ---------- Al cambiar de tamaño ---------- */

  var alCambiar = function () {
    overlay.classList.remove('show');
    document.body.classList.remove('admin-menu-abierto');
    if (movil.matches) {
      // el rail es solo de escritorio: se ignora la preferencia mientras sea móvil
      aplicarRail(false);
    } else {
      sidebar.classList.remove('show');
      aplicarRail(leerPreferencia() === 'rail');
    }
    sincronizarAria();
  };
  if (movil.addEventListener) movil.addEventListener('change', alCambiar);
  else if (movil.addListener) movil.addListener(alCambiar);

  // Si el usuario cambia el sidebar en otra pestaña, esta se actualiza
  window.addEventListener('storage', function (e) {
    if (e.key !== LLAVE || movil.matches) return;
    aplicarRail(e.newValue === 'rail');
    sincronizarAria();
  });

  // El <head> ya aplicó la preferencia; aquí solo nos aseguramos y sincronizamos el aria
  if (!movil.matches) aplicarRail(leerPreferencia() === 'rail');
  sincronizarAria();
})();
</script>
