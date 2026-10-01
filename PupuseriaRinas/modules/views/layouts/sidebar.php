<?php
/**
 * Sidebar del admin. Al colapsar no desaparece: queda un rail angosto
 * (--admin-rail-w) con solo iconos. Cada icono es un <i class="fa-solid ...">
 * para poder cambiarlo por el que se necesite sin tocar el resto.
 */
$items = [
    'pos'       => ['href' => 'pos.php',       'label' => 'POS',           'icon' => 'fa-cash-register'],
    'dashboard' => ['href' => 'index.php',     'label' => 'Dashboard',     'icon' => 'fa-table-cells-large'],
    'productos' => ['href' => 'productos.php', 'label' => 'Productos',    'icon' => 'fa-basket-shopping'],
    'pedidos'   => ['href' => 'pedidos.php',   'label' => 'Pedidos',      'icon' => 'fa-receipt'],
    'horarios'  => ['href' => 'horarios.php',  'label' => 'Horarios',     'icon' => 'fa-clock'],
    'usuarios'  => ['href' => 'usuarios.php',  'label' => 'Usuarios',     'icon' => 'fa-users'],
    'config'    => ['href' => 'config.php',    'label' => 'Configuracion','icon' => 'fa-gear'],
];
$u = usuario_actual();
?>
<aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar-head">
        <button class="admin-sidebar-toggle" type="button" id="adminSidebarClose" aria-label="Colapsar menú" aria-controls="adminSidebar">
            <i class="fa-solid fa-angles-left  admin-ico-colapsar" aria-hidden="true"></i>
            <i class="fa-solid fa-angles-right admin-ico-expandir" aria-hidden="true"></i>
        </button>
        <img src="../assets/img/logo.jpg" alt="Rinas" class="admin-logo">
        <div class="admin-brand">
            <div class="admin-brand-title">Pupusería Rinas</div>
            <div class="admin-brand-sub">Panel administrativo</div>
        </div>
    </div>

    <nav class="admin-nav" aria-label="Secciones del panel">
        <?php foreach ($items as $key => $it): ?>
        <a class="admin-nav-link <?= ($admin_active ?? '') === $key ? 'active' : '' ?>"
           href="<?= $it['href'] ?>" title="<?= $it['label'] ?>"
           <?= ($admin_active ?? '') === $key ? 'aria-current="page"' : '' ?>>
            <i class="fa-solid <?= $it['icon'] ?>" aria-hidden="true"></i>
            <span class="admin-nav-label"><?= $it['label'] ?></span>
        </a>
        <?php endforeach; ?>
    </nav>

    <div class="admin-sidebar-foot">
        <div class="admin-user" title="<?= htmlspecialchars($u['nombre'] ?? 'Admin') ?>">
            <span class="admin-user-badge"><?= htmlspecialchars(strtoupper(mb_substr($u['nombre'] ?? 'A', 0, 1))) ?></span>
            <div class="admin-user-info">
                <div class="admin-user-name"><?= htmlspecialchars($u['nombre'] ?? 'Admin') ?></div>
                <div class="admin-user-email"><?= htmlspecialchars($u['email'] ?? '') ?></div>
            </div>
        </div>
        <a href="logout.php" class="admin-logout" title="Salir">
            <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
            <span>Salir</span>
        </a>
    </div>
</aside>
<div class="admin-overlay" id="adminOverlay"></div>
