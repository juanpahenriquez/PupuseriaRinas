<?php
// Previsualización con datos de mentira: sirve para rediseñar la vista de
// pedidos sin tocar la BD ni pasar por login. Borrar cuando ya no se use.
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/util.php';

$_SESSION = [];
$_GET['p'] = $_GET['p'] ?? '1';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['PHP_SELF'] = '/PupuseriaRinas/admin/pedidos.php';

$estados = ['pendiente', 'cocina', 'listo', 'entregado'];

$clientes = ['María López', 'Carlos Ramírez', 'Ana Hernández', 'Jorge Mejía', 'Lucía Peralta', 'Ricardo Ávila', 'Fernanda Castillo', 'Óscar Medina', 'Paula Aguilar', 'Diego Solano'];
$detalles = [
    '3× Revuelta, 2× Queso',
    '2× Frijol con queso, 1× Horchata',
    '4× Chicharrón, 2× Yuca frita',
    '1× Loroco, 1× Pupusa de bebida',
    '6× Revuelta',
    '2× Queso, 1× Atol de morita',
    '3× Cerdo, 2× Ensalada',
    '5× Revuelta, 3× Queso',
    '2× Chicharrón',
    '4× Frijol, 2× Revuelta, 1× Jugo de maracuyá',
];
$precios = [
    ['Revuelta' => 1.50, 'Queso' => 1.50],
    ['Frijol con queso' => 1.50, 'Horchata' => 1.25],
    ['Chicharrón' => 1.75, 'Yuca frita' => 2.00],
    ['Loroco' => 1.75, 'Pupusa de bebida' => 1.50],
    ['Revuelta' => 1.50],
    ['Queso' => 1.50, 'Atol de morita' => 1.75],
    ['Cerdo' => 1.75, 'Ensalada' => 2.25],
    ['Revuelta' => 1.50, 'Queso' => 1.50],
    ['Chicharrón' => 1.75],
    ['Frijol' => 1.50, 'Revuelta' => 1.50, 'Jugo de maracuyá' => 2.50],
];
$telefonos = ['7012-3344', '6644-1290', '7890-5566', '', '7123-9988', '2233-4455', '', '8899-0011', '7555-1212', ''];

$pedidos = [];
for ($i = 0; $i < 24; $i++) {
    $id = 24 - $i;
    $estado = ['pendiente', 'cocina', 'listo', 'entregado'][(int) ($i / 6)];
    $detalle = $detalles[$i % count($detalles)];
    // arma las líneas como las devolvería el controlador (pedido_items)
    $items = [];
    foreach ((preg_split('/,\s*(?=\d+\s*[x×]\s*)/u', $detalle) ?: []) as $k => $parte) {
        if (!preg_match('/^(\d+)\s*[x×]\s*(.+)$/u', trim($parte), $m)) {
            continue;
        }
        $nombre = trim($m[2]);
        $listaPrecios = $precios[$i % count($precios)];
        $precio = $listaPrecios[$nombre] ?? 1.50;
        $items[] = ['pedido_id' => $id, 'producto_id' => $k + 1, 'nombre' => $nombre, 'cantidad' => (int) $m[1], 'precio' => $precio];
    }
    $pedidos[] = [
        'id'         => $id,
        'folio'      => '#' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
        'cliente'    => $clientes[$i % count($clientes)],
        'telefono'   => $telefonos[$i % count($telefonos)],
        'notas'      => $i % 4 === 0 ? 'Sin cebolla y extra queso porfa' : '',
        'tipo'       => $i % 2 ? 'llevar' : 'recoger',
        'detalle'    => $detalle,
        'items'      => $items,
        'total'      => number_format(1.5 + ($i % 7) * 3.25, 2),
        'estado'     => $estado,
        'creado'     => date('Y-m-d H:i:s', strtotime('-' . ($i * 37) . ' minutes')),
    ];
}

$paginacion = [
    'base' => 'pedidos.php',
    'q' => '',
    'params' => ['tipo' => '', 'estado' => ''],
    'total' => 24,
    'porPagina' => 10,
    'pagina' => (int) ($_GET['p'] ?? 1),
    'etiqueta' => 'pedidos',
    'placeholder' => 'Buscar por cliente, teléfono, folio o producto…',
];

$fTipo = $_GET['tipo'] ?? '';
$fEstado = $_GET['estado'] ?? '';
$q = '';
$contados = ['total' => 24, 'recoger' => 12, 'llevar' => 12, 'pendiente' => 6, 'cocina' => 6, 'listo' => 6, 'entregado' => 6];
$productosModal = [
    ['id' => 1, 'nombre' => 'Pupusa Revuelta', 'precio' => 1.50],
    ['id' => 2, 'nombre' => 'Pupusa de Queso', 'precio' => 1.50],
    ['id' => 3, 'nombre' => 'Pupusa de Chicharrón', 'precio' => 1.75],
    ['id' => 4, 'nombre' => 'Horchata', 'precio' => 1.25],
];
$flash_ok = null;
$flash_err = null;
?><!doctype html>
<html lang="es">
<head>
<?php $page_title = 'Pedidos (preview)'; include __DIR__ . '/../modules/views/layouts/head-admin.php'; ?>
</head>
<body class="admin-body">
<?php $admin_active = 'pedidos'; include __DIR__ . '/../modules/views/layouts/sidebar.php'; ?>
<div class="admin-main">
<?php $admin_title = 'Pedidos'; $admin_sub = '24 en total · 18 abiertos'; include __DIR__ . '/../modules/views/layouts/header-admin.php'; ?>
<main class="admin-content"><?php include __DIR__ . '/../modules/views/admin/pedidos.php'; ?></main>
</div>
</body>
</html>
