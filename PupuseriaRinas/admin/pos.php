<?php
/**
 * POS — punto de venta para el personal.
 * Crea pedidos en la tabla `pedidos` (estado 'pendiente') y permite
 * avanzar los estados desde la misma vista.
 */
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../php/enlaces.php';
requerir_login();

$pdo = db();
$flash_ok  = leer_flash('ok');
$flash_err = leer_flash('err');
$abrirPanel = leer_flash('abrir') === '1';   // vuelve a abrir el panel de estados

$estados   = ['pendiente', 'cocina', 'listo', 'entregado'];
$siguiente = ['pendiente' => 'cocina', 'cocina' => 'listo', 'listo' => 'entregado'];

// — Nombre del negocio (configuracion.nombre; si no, el de la app) —
$negocio = 'Pupusería Rinas';
$filaNegocio = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'nombre' LIMIT 1")->fetchColumn();
if ($filaNegocio !== false && trim((string) $filaNegocio) !== '') {
    $negocio = (string) $filaNegocio;
}

// — Nombre y logo para la cabecera del panel derecho —
$logo = null;
$filaLogo = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'logo' LIMIT 1")->fetchColumn();
if ($filaLogo !== false && trim((string) $filaLogo) !== '') {
    $logo = '../' . ltrim((string) $filaLogo, '/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $action = $_POST['action'] ?? '';

    /* ---------------- COBRAR: crea el pedido ---------------- */
    if ($action === 'cobrar') {
        $tipo     = $_POST['tipo'] ?? '';
        $cliente  = trim((string) ($_POST['cliente'] ?? ''));
        $telefono = trim((string) ($_POST['telefono'] ?? ''));
        $notas    = trim((string) ($_POST['notas'] ?? ''));
        $ids      = $_POST['linea_id'] ?? [];
        $cants    = $_POST['linea_cant'] ?? [];

        $errors = [];
        if (!in_array($tipo, ['recoger', 'llevar'], true)) {
            $errors[] = 'Elige si el pedido es para recoger o para llevar';
        }
        if ($cliente === '') {
            $errors[] = 'Escribe el nombre del cliente';
        }
        if (mb_strlen($cliente) > 100) {
            $cliente = mb_substr($cliente, 0, 100);
        }
        if ($telefono !== '' && !preg_match('/^[0-9\-\s+]{7,20}$/', $telefono)) {
            $errors[] = 'Teléfono inválido';
        }
        if (mb_strlen($notas) > 255) {
            $notas = mb_substr($notas, 0, 255);
        }
        if (!is_array($ids) || !count($ids)) {
            $errors[] = 'Agrega al menos un producto';
        }

        // Las líneas se validan contra la BD: el precio nunca viene del cliente
        $lineas = [];
        $total  = 0.0;
        if (is_array($ids)) {
            $pStmt = $pdo->prepare("SELECT id, nombre, precio FROM productos WHERE id = ? AND estado = 'Activo'");
            foreach (array_values($ids) as $k => $pid) {
                $pid = (int) $pid;
                $qty = (int) ($cants[$k] ?? 0);
                if ($pid <= 0) {
                    continue;
                }
                if ($qty < 1 || $qty > 999) {
                    $errors[] = 'Cantidad inválida en un producto';
                    break;
                }
                $pStmt->execute([$pid]);
                $prod = $pStmt->fetch();
                if (!$prod) {
                    $errors[] = 'Producto no disponible (#' . $pid . ')';
                    break;
                }
                $lineas[] = [
                    'id'       => (int) $prod['id'],
                    'nombre'   => $prod['nombre'],
                    'precio'   => (float) $prod['precio'],
                    'cantidad' => $qty,
                ];
                $total += (float) $prod['precio'] * $qty;
            }
        }

        if (empty($errors) && $lineas) {
            $nuevoId = 0;
            $pdo->beginTransaction();
            try {
                $ins = $pdo->prepare("INSERT INTO pedidos (tipo, cliente, telefono, notas, total, estado) VALUES (?,?,?,?,?, 'pendiente')");
                $ins->execute([$tipo, $cliente, $telefono, $notas, number_format($total, 2, '.', '')]);
                $nuevoId = (int) $pdo->lastInsertId();
                $pdo->prepare("UPDATE pedidos SET folio = CONCAT('#', LPAD(?,4,'0')) WHERE id = ?")->execute([$nuevoId, $nuevoId]);
                $iStmt = $pdo->prepare("INSERT INTO pedido_items (pedido_id, producto_id, nombre, cantidad, precio) VALUES (?,?,?,?,?)");
                foreach ($lineas as $l) {
                    $iStmt->execute([$nuevoId, $l['id'], $l['nombre'], $l['cantidad'], number_format($l['precio'], 2, '.', '')]);
                }
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                $nuevoId = 0;
                flash('err', 'No se pudo guardar el pedido: ' . $e->getMessage());
            }
            if ($nuevoId > 0) {
                flash('ok', 'Pedido #' . str_pad((string) $nuevoId, 4, '0', STR_PAD_LEFT)
                    . ' cobrado · $' . number_format($total, 2));
            }
            header('Location: pos.php');
            exit;
        }
        flash('err', $errors ? implode(' · ', $errors) : 'No se pudo crear el pedido');
        header('Location: pos.php');
        exit;
    }

    /* ---------------- AVANZAR ESTADO ---------------- */
    if ($action === 'estado') {
        $id    = (int) ($_POST['id'] ?? 0);
        $nuevo = $_POST['estado'] ?? '';
        if ($id > 0 && in_array($nuevo, $estados, true)) {
            $pdo->prepare("UPDATE pedidos SET estado = ? WHERE id = ?")->execute([$nuevo, $id]);
            flash('ok', 'Pedido #' . str_pad((string) $id, 4, '0', STR_PAD_LEFT) . ' → ' . $nuevo);
        } else {
            flash('err', 'Estado no válido');
        }
        // Vuelve a abrir el panel de estados para seguir avanzando el resto
        flash('abrir', '1');
        header('Location: pos.php');
        exit;
    }
}

/* ---------------- Datos para la vista ---------------- */

/* Pedidos en curso: los que todavía no se entregan. Se ordenan por estado
   (primero lo pendiente) y luego por antigüedad, que es como los atiende la caja. */
$abiertos = $pdo->query("SELECT p.id, p.folio, p.tipo, p.cliente, p.telefono, p.notas,
                                p.total, p.estado, p.creado,
                                COALESCE(GROUP_CONCAT(CONCAT(i.cantidad,'× ',i.nombre) SEPARATOR ', '),'—') AS detalle
                         FROM pedidos p LEFT JOIN pedido_items i ON i.pedido_id = p.id
                         WHERE p.estado IN ('pendiente','cocina','listo')
                         GROUP BY p.id
                         ORDER BY FIELD(p.estado,'pendiente','cocina','listo'), p.creado ASC")->fetchAll();

$conteoAbiertos = ['pendiente' => 0, 'cocina' => 0, 'listo' => 0];
foreach ($abiertos as $a) {
    if (array_key_exists($a['estado'], $conteoAbiertos)) {
        $conteoAbiertos[$a['estado']]++;
    }
}

// Solo productos activos: los agotados no se venden
$productos = $pdo->query("SELECT id, nombre, descripcion, precio, categoria, imagen
                          FROM productos WHERE estado = 'Activo'
                          ORDER BY categoria, nombre")->fetchAll();

$categorias = [];
foreach ($productos as $p) {
    $categorias[$p['categoria']] = true;
}
$categorias = array_keys($categorias);
sort($categorias);


?><!doctype html>
<html lang="es">
<head>
<?php $page_title = 'POS'; include __DIR__ . '/../modules/views/layouts/head-admin.php'; ?>
<link rel="stylesheet" href="../assets/css/pos.css?v=6">
<script>
/* Modo pantalla completa del POS. Se aplica ANTES de pintar el <body> para que
   el sidebar no aparezca un instante y se oculte después (mismo truco que usa
   head-admin.php con la preferencia del menú lateral). */
(function () {
  try {
    if (localStorage.getItem('rinas_pos_fullscreen') === '1') {
      document.documentElement.classList.add('pos-fullscreen');
    }
  } catch (e) { /* modo privado o storage bloqueado: solo dura esta sesión */ }
})();
</script>
</head>
<body class="admin-body pos-body">
<?php $admin_active = 'pos'; include __DIR__ . '/../modules/views/layouts/sidebar.php'; ?>
<div class="admin-main">
<?php
$admin_title = 'POS';
$admin_sub   = 'Punto de venta';
include __DIR__ . '/../modules/views/layouts/header-admin.php';
?>
<main class="admin-content pos-content">
<?php include __DIR__ . '/../modules/views/admin/pos.php'; ?>
</main>
</div>
</body>
</html>
