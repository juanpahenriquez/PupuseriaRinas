<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/auth.php';
requerir_login();

$pdo = db();
$flash_ok = leer_flash('ok');
$flash_err = leer_flash('err');

/* ══════════════════════════════════════════════════════════════════
   1. CATÁLOGOS DEL DOMINIO
   La lista de estados es la política del pedido y vive aquí, una sola vez:
     · 'id'      → lo que valida el cambio de estado y el filtro GET;
     · 'label'   → el texto de las píldoras, el <select> y el modal;
     · 'badge'   → la clase del distintivo;
     · 'clase'   → la clase del <select> de la fila;
     · 'icon' y 'titulo' → el toast tras un cambio de estado.
   Quien añada un estado lo añade en esta tabla y la vista lo refleja sola.
   ══════════════════════════════════════════════════════════════════ */
$estados = [
    'pendiente' => ['label' => 'Pendiente', 'badge' => 'badge-pendiente', 'clase' => 'pedidos-estado--pendiente', 'icon' => 'info',    'titulo' => 'Pedido pendiente'],
    'cocina'    => ['label' => 'En cocina', 'badge' => 'badge-cocina',    'clase' => 'pedidos-estado--cocina',    'icon' => 'warning', 'titulo' => 'Pedido en cocina'],
    'listo'     => ['label' => 'Listo',      'badge' => 'badge-listo',     'clase' => 'pedidos-estado--listo',     'icon' => 'success', 'titulo' => 'Pedido listo'],
    'entregado' => ['label' => 'Entregado',  'badge' => 'badge-entregado', 'clase' => 'pedidos-estado--entregado', 'icon' => 'success', 'titulo' => 'Pedido entregado'],
];

/* Los tipos no llevan etiqueta aquí: eso es presentación y lo pinta la
   vista. Aquí solo el conjunto que valida el alta y el filtro GET. */
$tiposPedido = ['llevar', 'recoger'];

/* ══════════════════════════════════════════════════════════════════
   2. REGLAS DEL ALTA
   Una constante por control, cada una con la columna que defiende: sin
   acotar, el INSERT revienta con MySQL en modo estricto.
   ══════════════════════════════════════════════════════════════════ */
const PED_CLIENTE_MAX  = 120;                       // VARCHAR(120)
const PED_TELEFONO_RE  = '/^[0-9\-\s+]{7,20}$/';     // VARCHAR(30); de 7 a 20
const PED_NOTAS_MAX    = 255;                       // VARCHAR(255)
const PED_CANTIDAD_MIN = 1;                         // INT
const PED_CANTIDAD_MAX = 999;                       // INT

/* ══════════════════════════════════════════════════════════════════
   3. AYUDAS DE LAS ACCIONES
   ══════════════════════════════════════════════════════════════════ */

/* PRG: vuelve al listado conservando los filtros (campo `back`) y corta. */
$volver = function (string $defecto = 'pedidos.php'): void {
    header('Location: ' . url_retorno($_POST['back'] ?? '', $defecto));
    exit;
};

/*
 * Valida y normaliza el alta. Devuelve datos, líneas, total y errores.
 * Todas las reglas de los campos viven dentro de esta función: quien
 * llama solo decide si guarda o si pinta los errores.
 */
$validarAlta = function (array $post) use ($pdo, $tiposPedido): array {
    $datos = [
        'cliente'  => trim($post['cliente'] ?? ''),
        'telefono' => trim($post['telefono'] ?? ''),
        'tipo'     => in_array($post['tipo'] ?? '', $tiposPedido, true) ? $post['tipo'] : 'recoger',
        'notas'    => trim($post['notas'] ?? ''),
    ];

    $errors = [];
    if ($datos['cliente'] === '') {
        $errors[] = 'Nombre del cliente requerido';
    } elseif (mb_strlen($datos['cliente']) > PED_CLIENTE_MAX) {
        $errors[] = 'El nombre del cliente no puede pasar de ' . PED_CLIENTE_MAX . ' caracteres';
    }
    // El teléfono no se trunca en silencio: cortarlo deja un número falso.
    if ($datos['telefono'] !== '' && !preg_match(PED_TELEFONO_RE, $datos['telefono'])) {
        $errors[] = 'Teléfono inválido (solo dígitos, guiones o espacios)';
    }
    if (mb_strlen($datos['notas']) > PED_NOTAS_MAX) {
        $errors[] = 'Las notas no pueden pasar de ' . PED_NOTAS_MAX . ' caracteres';
    }

    $ids   = $post['producto_id'] ?? [];
    $cants = $post['cantidad'] ?? [];
    if (!is_array($ids) || !count($ids)) {
        $errors[] = 'Agrega al menos un producto';
    }

    // Las líneas se validan contra la BD: el precio nunca viene del cliente.
    $lineas = [];
    $total  = 0.0;
    if (is_array($ids)) {
        $pStmt = $pdo->prepare("SELECT id, nombre, precio FROM productos WHERE id = ? AND estado = 'Activo'");
        foreach ($ids as $k => $pid) {
            $pid = (int) $pid;
            $qty = (int) ($cants[$k] ?? 0);
            if ($pid <= 0) {
                continue;
            }
            if ($qty < PED_CANTIDAD_MIN || $qty > PED_CANTIDAD_MAX) {
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

    return ['datos' => $datos, 'lineas' => $lineas, 'total' => $total, 'errors' => $errors];
};

/* Guarda cabecera y líneas en una sola transacción y devuelve el id nuevo.
   No decide el aviso: si la BD falla, relanza y llama quien decide. */
$guardarAlta = function (array $datos, array $lineas, float $total) use ($pdo): int {
    $pdo->beginTransaction();
    try {
        $ins = $pdo->prepare("INSERT INTO pedidos (tipo, cliente, telefono, notas, total, estado) VALUES (?,?,?,?,?, 'pendiente')");
        $ins->execute([$datos['tipo'], $datos['cliente'], $datos['telefono'], $datos['notas'], number_format($total, 2, '.', '')]);
        $nuevoId = (int) $pdo->lastInsertId();
        $pdo->prepare("UPDATE pedidos SET folio = CONCAT('#', LPAD(?,4,'0')) WHERE id = ?")->execute([$nuevoId, $nuevoId]);
        $iStmt = $pdo->prepare("INSERT INTO pedido_items (pedido_id, producto_id, nombre, cantidad, precio) VALUES (?,?,?,?,?)");
        foreach ($lineas as $l) {
            $iStmt->execute([$nuevoId, $l['id'], $l['nombre'], $l['cantidad'], number_format($l['precio'], 2, '.', '')]);
        }
        $pdo->commit();
        return $nuevoId;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
};

/* ══════════════════════════════════════════════════════════════════
   4. ACCIONES POST
   Tres acciones y tres canales de aviso, cada uno con su motivo:
     · alta con datos inválidos -> $flash_err en línea, SIN redirección,
       para que la vista reabra el modal con lo que el cajero ya escribió;
     · alta con fallo de BD      -> flash('err') + $volver();
     · cambio de estado y borrado -> flash('ok' | 'toast') + $volver().
   ══════════════════════════════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $action = $_POST['action'] ?? '';

    /* ---- Crear un pedido ---- */
    if ($action === 'crear') {
        $alta = $validarAlta($_POST);

        if ($alta['errors'] || !$alta['lineas']) {
            $flash_err = $alta['errors']
                ? implode(' · ', $alta['errors'])
                : 'No se pudo crear el pedido — revisa los productos';
        } else {
            try {
                $nuevoId = $guardarAlta($alta['datos'], $alta['lineas'], $alta['total']);
            } catch (Throwable $e) {
                // No se filtra $e->getMessage(): puede exponer el esquema de la BD.
                flash('err', 'No se pudo guardar el pedido. Revisa los datos e inténtalo de nuevo.');
                $volver();
            }
            flash('ok', 'Pedido #' . str_pad((string) $nuevoId, 4, '0', STR_PAD_LEFT) . ' creado correctamente');
            $volver();
        }

    /* ---- Cambiar el estado ---- */
    } elseif ($action === 'estado') {
        $id     = (int) ($_POST['id'] ?? 0);
        $estado = (string) ($_POST['estado'] ?? '');
        $cfg    = $estados[$estado] ?? null;

        if ($id > 0 && $cfg !== null) {
            $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?")->execute([$estado, $id]);
            $icon   = $cfg['icon'];
            $titulo = $cfg['titulo'];
            $texto  = 'Pedido #' . str_pad((string) $id, 4, '0', STR_PAD_LEFT);
        } else {
            $icon   = 'error';
            $titulo = 'Estado no válido';
            $texto  = '';
        }
        // flash() solo acepta string: el toast viaja como JSON
        flash('toast', json_encode(['icon' => $icon, 'title' => $titulo, 'text' => $texto], JSON_UNESCAPED_UNICODE));
        $volver();

    /* ---- Eliminar un pedido ---- */
    } elseif ($action === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            $pdo->prepare("DELETE FROM pedidos WHERE id = ?")->execute([$id]);
            flash('ok', 'Pedido eliminado');
        }
        $volver();
    }
}

/* ══════════════════════════════════════════════════════════════════
   5. LISTADO — filtros GET y consulta
   ══════════════════════════════════════════════════════════════════ */
$fTipo = in_array($_GET['tipo'] ?? '', $tiposPedido, true) ? $_GET['tipo'] : '';
$fEstado = isset($estados[$_GET['estado'] ?? '']) ? $_GET['estado'] : '';
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
// La página no hace scroll: 7 filas caben en el alto libre de una pantalla
// normal. Subirlo obliga a recortar la tabla en pantallas bajas.
$pg = paginar($totalFiltrado, 7, pagina_get());

// LIMIT/OFFSET son enteros de paginar(): seguros de interpolar
$sql = "SELECT p.*, COALESCE(GROUP_CONCAT(CONCAT(i.cantidad,'× ',i.nombre) SEPARATOR ', '),'—') detalle
        FROM pedidos p LEFT JOIN pedido_items i ON i.pedido_id = p.id{$sqlWhere}
        GROUP BY p.id ORDER BY p.id DESC LIMIT {$pg['porPagina']} OFFSET {$pg['offset']}";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pedidos = $stmt->fetchAll();

// Líneas reales del pedido (cantidad, producto, precio) para el modal de detalle.
// Un solo extra por página, con la misma lista de folios ya consultada.
$pedidosIds = array_map('intval', array_column($pedidos, 'id'));
if ($pedidosIds) {
    $marcas = implode(',', array_fill(0, count($pedidosIds), '?'));
    $iStmt = $pdo->prepare("SELECT pedido_id, nombre, cantidad, precio FROM pedido_items WHERE pedido_id IN ({$marcas}) ORDER BY id");
    $iStmt->execute($pedidosIds);
    $itemsPorPedido = [];
    foreach ($iStmt->fetchAll() as $it) {
        $itemsPorPedido[(int) $it['pedido_id']][] = $it;
    }
    foreach ($pedidos as $k => $ped) {
        $pedidos[$k]['items'] = $itemsPorPedido[(int) $ped['id']] ?? [];
    }
}

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

// Contadores de las píldoras: se recorren los catálogos, no se reescriben a mano
$contar = function (string $col, string $val) use ($pdo): int {
    $s = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE {$col} = ?");
    $s->execute([$val]);
    return (int) $s->fetchColumn();
};
$contados = ['total' => $totalPedidos];
foreach ($tiposPedido as $t) {
    $contados[$t] = $contar('tipo', $t);
}
foreach (array_keys($estados) as $e) {
    $contados[$e] = $contar('estado', $e);
}
?><!doctype html>
<html lang="es">
<head>
<?php $page_title='Pedidos'; include __DIR__ . "/../modules/views/layouts/head-admin.php"; ?>
</head>
<body class="admin-body admin-pedidos-body">
<?php $admin_active='pedidos'; include __DIR__ . "/../modules/views/layouts/sidebar.php"; ?>
<div class="admin-main">
<?php $admin_title='Pedidos'; $admin_sub=$totalPedidos . ' en total · ' . $pendientesHoy . ' abiertos'; include __DIR__ . "/../modules/views/layouts/header-admin.php"; ?>
<main class="admin-content"><?php include __DIR__ . "/../modules/views/admin/pedidos.php"; ?></main>
</div>
</body>
</html>