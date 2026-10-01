<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/auth.php';
requerir_login();

$pdo = db();
$ticketMeta = 8.00;

// — KPIs simples —
$ventas_hoy = (float) $pdo->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(creado)=CURDATE()")->fetchColumn();
$ventas_ayer = (float) $pdo->query("SELECT COALESCE(SUM(total),0) FROM pedidos WHERE DATE(creado)=DATE_SUB(CURDATE(), INTERVAL 1 DAY)")->fetchColumn();
$pend = $pdo->query("SELECT COUNT(*) c,
        COALESCE(SUM(tipo='llevar'),0) lv,
        COALESCE(SUM(tipo='recoger'),0) rg
     FROM pedidos WHERE estado IN ('pendiente','cocina','listo')")->fetch();
$ticket = (float) $pdo->query("SELECT COALESCE(AVG(total),0) FROM pedidos WHERE DATE(creado)=CURDATE()")->fetchColumn();
$productos_activos = (int) $pdo->query("SELECT COUNT(*) FROM productos WHERE estado='Activo'")->fetchColumn();

// — Canal (pedidos de hoy por tipo) —
$canal = $pdo->query("SELECT COALESCE(SUM(tipo='llevar'),0) lv, COALESCE(SUM(tipo='recoger'),0) rg
     FROM pedidos WHERE DATE(creado)=CURDATE()")->fetch();

// — Semana (lunes a domingo de la semana actual) —
$inicioSem = date('Y-m-d', strtotime('monday this week'));
$ventas_semana = array_fill(0, 7, 0.0);
$pedidos_semana = array_fill(0, 7, 0);
$stmt = $pdo->prepare("SELECT DATE(creado) f, COALESCE(SUM(total),0) v, COUNT(*) c
     FROM pedidos WHERE DATE(creado) BETWEEN ? AND DATE_ADD(?, INTERVAL 6 DAY)
     GROUP BY DATE(creado)");
$stmt->execute([$inicioSem, $inicioSem]);
foreach ($stmt as $r) {
    $i = (int) round((strtotime($r['f']) - strtotime($inicioSem)) / 86400);
    if ($i >= 0 && $i < 7) {
        $ventas_semana[$i] = (float) $r['v'];
        $pedidos_semana[$i] = (int) $r['c'];
    }
}

// — Mes por semanas (Sem 1 = días 1–7, etc.) —
$ventas_mes = array_fill(0, 4, 0.0);
$pedidos_mes = array_fill(0, 4, 0);
$stmt = $pdo->query("SELECT CASE WHEN DAY(creado)<=7 THEN 1 WHEN DAY(creado)<=14 THEN 2
             WHEN DAY(creado)<=21 THEN 3 ELSE 4 END sem,
         COALESCE(SUM(total),0) v, COUNT(*) c
     FROM pedidos
     WHERE YEAR(creado)=YEAR(CURDATE()) AND MONTH(creado)=MONTH(CURDATE())
     GROUP BY sem");
foreach ($stmt as $r) {
    $i = (int) $r['sem'] - 1;
    $ventas_mes[$i] = (float) $r['v'];
    $pedidos_mes[$i] = (int) $r['c'];
}

// — Top productos (unidades vendidas) —
$top = $pdo->query("SELECT p.nombre, COALESCE(SUM(i.cantidad),0) uds
     FROM pedido_items i JOIN productos p ON p.id = i.producto_id
     GROUP BY i.producto_id, p.nombre ORDER BY uds DESC LIMIT 5")->fetchAll();
$maxUds = $top ? (int) max(array_column($top, 'uds')) : 0;
$top_productos = [];
foreach ($top as $r) {
    $top_productos[] = [
        'nombre' => $r['nombre'],
        'uds'    => (int) $r['uds'],
        'pct'    => $maxUds > 0 ? max(4, (int) round($r['uds'] / $maxUds * 100)) : 0,
    ];
}

// — Pedidos recientes (con detalle de ítems) —
$recs = $pdo->query("SELECT p.*, COALESCE(GROUP_CONCAT(CONCAT(i.cantidad,'× ',i.nombre) SEPARATOR ', '),'—') detalle
     FROM pedidos p LEFT JOIN pedido_items i ON i.pedido_id = p.id
     GROUP BY p.id ORDER BY p.id DESC LIMIT 5")->fetchAll();
$tipoLabel = ['llevar' => 'Llevar', 'recoger' => 'Recoger'];
$pedidos_recientes = [];
foreach ($recs as $r) {
    $pedidos_recientes[] = [
        ($r['folio'] !== '' ? $r['folio'] : '#' . $r['id']),
        $tipoLabel[$r['tipo']] ?? $r['tipo'],
        $r['detalle'],
        (float) $r['total'],
        $r['estado'],
        hace($r['creado']),
    ];
}

// — Horario de hoy —
$hStmt = $pdo->prepare("SELECT * FROM horarios WHERE dia = ?");
$hStmt->execute([(int) date('N')]);
$hoy = $hStmt->fetch() ?: null;
$rangoHoy = rango_dia($hoy);
$abierta = esta_abierto($hoy);

$mock = [
    'ventas_hoy'          => $ventas_hoy,
    'ventas_ayer'         => $ventas_ayer,
    'pedidos_pendientes'  => (int) $pend['c'],
    'pedidos_llevar'      => (int) $pend['lv'],
    'pedidos_recoger'     => (int) $pend['rg'],
    'canal_llevar'        => (int) $canal['lv'],
    'canal_recoger'       => (int) $canal['rg'],
    'ticket'              => $ticket,
    'productos_activos'   => $productos_activos,
    'ventas_semana'       => $ventas_semana,
    'pedidos_semana'      => $pedidos_semana,
    'ventas_mes'          => $ventas_mes,
    'pedidos_mes'         => $pedidos_mes,
    'dias'                => ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
    'semanas'             => ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'],
    'ticket_meta'         => $ticketMeta,
    'ticket_meta_pct'     => ($ticket > 0 && $ticketMeta > 0) ? min(100, (int) round($ticket / $ticketMeta * 100)) : 0,
    'top_productos'       => $top_productos,
    'pedidos_recientes'   => $pedidos_recientes,
    'total_pedidos'       => (int) $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn(),
];
?><!doctype html>
<html lang="es">
<head>
<?php $page_title='Dashboard'; include __DIR__ . "/../modules/views/layouts/head-admin.php"; ?>
</head>
<body class="admin-body">
<?php $admin_active='dashboard'; include __DIR__ . "/../modules/views/layouts/sidebar.php"; ?>
<div class="admin-main">
<?php $admin_title='Dashboard'; $admin_sub='Resumen operativo · ' . date('d/m/Y'); include __DIR__ . "/../modules/views/layouts/header-admin.php"; ?>
<main class="admin-content"><?php include __DIR__ . "/../modules/views/admin/dashboard.php"; ?></main>
</div>
</body>
</html>
