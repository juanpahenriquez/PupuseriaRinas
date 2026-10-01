<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/auth.php';
requerir_login();

$pdo = db();
$estados = ['pendiente', 'cocina', 'listo', 'entregado'];
$flash_ok = leer_flash('ok');
$flash_err = leer_flash('err');

// — Acciones POST —
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'crear') {
        $cliente = trim($_POST['cliente'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $tipo = in_array($_POST['tipo'] ?? '', ['llevar', 'recoger'], true) ? $_POST['tipo'] : 'recoger';
        $notas = trim($_POST['notas'] ?? '');
        $ids = $_POST['producto_id'] ?? [];
        $cants = $_POST['cantidad'] ?? [];
        $errors = [];
        if ($cliente === '') {
            $errors[] = 'Nombre del cliente requerido';
        }
        if (!is_array($ids) || !count($ids)) {
            $errors[] = 'Agrega al menos un producto';
        }

        $lineas = [];
        $total = 0.0;
        if (is_array($ids)) {
            $pStmt = $pdo->prepare("SELECT id, nombre, precio FROM productos WHERE id = ? AND estado = 'Activo'");
            foreach ($ids as $k => $pid) {
                $pid = (int) $pid;
                $qty = (int) ($cants[$k] ?? 0);
                if ($pid <= 0) {
                    continue;
                }
                if ($qty < 1) {
                    $errors[] = 'Cantidad inválida en un producto';
                    break;
                }
                $pStmt->execute([$pid]);
                $prod = $pStmt->fetch();
                if (!$prod) {
                    $errors[] = 'Producto no disponible (#' . $pid . ')';
                    break;
                }
                $lineas[] = ['id' => (int) $prod['id'], 'nombre' => $prod['nombre'], 'precio' => (float) $prod['precio'], 'cantidad' => $qty];
                $total += (float) $prod['precio'] * $qty;
            }
        }

        if (empty($errors) && $lineas) {
            $pid = 0;
            $pdo->beginTransaction();
            try {
                $ins = $pdo->prepare("INSERT INTO pedidos (tipo, cliente, telefono, notas, total, estado) VALUES (?,?,?,?,?, 'pendiente')");
                $ins->execute([$tipo, $cliente, $telefono, $notas, number_format($total, 2, '.', '')]);
                $pid = (int) $pdo->lastInsertId();
                $pdo->prepare("UPDATE pedidos SET folio = CONCAT('#', LPAD(?,4,'0')) WHERE id = ?")->execute([$pid, $pid]);
                $iStmt = $pdo->prepare("INSERT INTO pedido_items (pedido_id, producto_id, nombre, cantidad, precio) VALUES (?,?,?,?,?)");
                foreach ($lineas as $l) {
                    $iStmt->execute([$pid, $l['id'], $l['nombre'], $l['cantidad'], number_format($l['precio'], 2, '.', '')]);
                }
                $pdo->commit();
            } catch (Exception $e) {
                $pdo->rollBack();
                $pid = 0;
                $flash_err = 'No se pudo guardar el pedido: ' . $e->getMessage();
            }
            if ($pid > 0) {
                flash('ok', 'Pedido #' . str_pad((string) $pid, 4, '0', STR_PAD_LEFT) . ' creado correctamente');
                header('Location: pedidos.php');
                exit;
            }
        } else {
            $flash_err = $errors ? implode(' · ', $errors) : 'No se pudo crear el pedido — revisa los productos';
        }
    } elseif ($action === 'estado') {
        $id = (int) ($_POST['id'] ?? 0);
        $estado = $_POST['estado'] ?? '';
        if ($id > 0 && in_array($estado, $estados, true)) {
            $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?")->execute([$estado, $id]);
            $iconToast = ['pendiente' => 'info', 'cocina' => 'warning', 'listo' => 'success', 'entregado' => 'success'][$estado] ?? 'info';
            $tituloToast = [
                'pendiente' => 'Pedido pendiente',
                'cocina'    => 'Pedido en cocina',
                'listo'     => 'Pedido listo',
                'entregado' => 'Pedido entregado',
            ][$estado] ?? 'Estado actualizado';
            // flash() solo acepta string: el toast viaja como JSON
            flash('toast', json_encode([
                'icon'  => $iconToast,
                'title' => $tituloToast,
                'text'  => 'Pedido #' . str_pad((string) $id, 4, '0', STR_PAD_LEFT),
            ], JSON_UNESCAPED_UNICODE));
        } else {
            flash('toast', json_encode([
                'icon'  => 'error',
                'title' => 'Estado no válido',
                'text'  => '',
            ], JSON_UNESCAPED_UNICODE));
        }
        $back = url_retorno($_POST['back'] ?? '', 'pedidos.php');
        header('Location: ' . $back);
        exit;
    } elseif ($action === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM pedidos WHERE id = ?")->execute([$id]);
            flash('ok', 'Pedido eliminado');
        }
        $back = url_retorno($_POST['back'] ?? '', 'pedidos.php');
        header('Location: ' . $back);
        exit;
    }
}

// — Filtros GET: tipo, estado y búsqueda (q) —
$fTipo = in_array($_GET['tipo'] ?? '', ['llevar', 'recoger'], true) ? $_GET['tipo'] : '';
$fEstado = in_array($_GET['estado'] ?? '', $estados, true) ? $_GET['estado'] : '';
$q = q_get();

$where = [];
$params = [];
if ($fTipo !== '') {
    $where[] = 'p.tipo = ?';
    $params[] = $fTipo;
}
if ($fEstado !== '') {
    $where[] = 'p.estado = ?';
    $params[] = $fEstado;
}
if ($q !== '') {
    // Busca en los datos del pedido y también en sus productos
    $like = like_param($q);
    $where[] = '(p.cliente LIKE ? OR p.telefono LIKE ? OR p.folio LIKE ? OR p.notas LIKE ?'
        . ' OR EXISTS (SELECT 1 FROM pedido_items i2 WHERE i2.pedido_id = p.id AND i2.nombre LIKE ?))';
    $params = array_merge($params, [$like, $like, $like, $like, $like]);
}
$sqlWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';

// Total filtrado + página actual
$stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos p{$sqlWhere}");
$stmt->execute($params);
$totalFiltrado = (int) $stmt->fetchColumn();
$pg = paginar($totalFiltrado, 10, pagina_get());

// LIMIT/OFFSET son enteros de paginar(): seguros de interpolar
$sql = "SELECT p.*, COALESCE(GROUP_CONCAT(CONCAT(i.cantidad,'× ',i.nombre) SEPARATOR ', '),'—') detalle
        FROM pedidos p LEFT JOIN pedido_items i ON i.pedido_id = p.id{$sqlWhere}
        GROUP BY p.id ORDER BY p.id DESC LIMIT {$pg['porPagina']} OFFSET {$pg['offset']}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

$paginacion = [
    'base' => 'pedidos.php',
    'q' => $q,
    'params' => ['tipo' => $fTipo, 'estado' => $fEstado],
    'total' => $totalFiltrado,
    'porPagina' => $pg['porPagina'],
    'pagina' => $pg['pagina'],
    'etiqueta' => 'pedidos',
    'placeholder' => 'Buscar por cliente, teléfono, folio o producto…',
];

$totalPedidos = (int) $pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
$pendientesHoy = (int) $pdo->query("SELECT COUNT(*) FROM pedidos WHERE estado IN ('pendiente','cocina','listo')")->fetchColumn();

// Productos activos para el modal de nuevo pedido
$productosModal = $pdo->query("SELECT id, nombre, precio FROM productos WHERE estado = 'Activo' ORDER BY categoria, nombre")->fetchAll();

// Contadores para las píldoras de filtro
$contar = function (string $col, string $val) use ($pdo): int {
    $s = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE {$col} = ?");
    $s->execute([$val]);
    return (int) $s->fetchColumn();
};
$contados = [
    'total' => $totalPedidos,
    'llevar' => $contar('tipo', 'llevar'),
    'recoger' => $contar('tipo', 'recoger'),
    'pendiente' => $contar('estado', 'pendiente'),
    'cocina' => $contar('estado', 'cocina'),
    'listo' => $contar('estado', 'listo'),
    'entregado' => $contar('estado', 'entregado'),
];
?><!doctype html>
<html lang="es">
<head>
<?php $page_title='Pedidos'; include __DIR__ . "/../modules/views/layouts/head-admin.php"; ?>
</head>
<body class="admin-body">
<?php $admin_active='pedidos'; include __DIR__ . "/../modules/views/layouts/sidebar.php"; ?>
<div class="admin-main">
<?php $admin_title='Pedidos'; $admin_sub=$totalPedidos . ' en total · ' . $pendientesHoy . ' abiertos'; include __DIR__ . "/../modules/views/layouts/header-admin.php"; ?>
<main class="admin-content"><?php include __DIR__ . "/../modules/views/admin/pedidos.php"; ?></main>
</div>
</body>
</html>
