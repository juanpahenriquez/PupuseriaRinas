<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/auth.php';
requerir_login();

$pdo = db();
$uploadDir = __DIR__ . '/../assets/img/productos';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function handleUpload($field, $uploadDir)
{
    if (!isset($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) return ['error' => 'Error al subir imagen'];
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/jpg' => 'jpg'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $_FILES[$field]['tmp_name']);
    finfo_close($finfo);
    if (!isset($allowed[$mime])) {
        $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) return ['error' => 'Formato no permitido (jpg, png, webp)'];
        $mimeExt = $ext === 'jpeg' ? 'jpg' : $ext;
    } else {
        $mimeExt = $allowed[$mime];
    }
    if ($_FILES[$field]['size'] > 5 * 1024 * 1024) return ['error' => 'Imagen muy grande (máx 5MB)'];
    $name = uniqid('prod_', true) . '.' . $mimeExt;
    $dest = $uploadDir . '/' . $name;
    if (!move_uploaded_file($_FILES[$field]['tmp_name'], $dest)) return ['error' => 'No se pudo guardar la imagen'];
    return ['path' => 'assets/img/productos/' . $name];
}

function imagenPorDefecto($categoria)
{
    if ($categoria === 'Bebidas') return 'assets/img/especialidad_loroco.png';
    if ($categoria === 'Complementos') return 'assets/img/images - Editado.png';
    return 'assets/img/pupa_camaron.png';
}

// POST crear / editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'crear') {
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = trim($_POST['precio'] ?? '');
        $categoria = trim($_POST['categoria'] ?? 'Pupusas');
        $estado = trim($_POST['estado'] ?? 'Activo');
        $errors = [];
        if ($nombre === '' || strlen($nombre) < 3) $errors[] = 'Nombre requerido (mín 3 caracteres)';
        if (!is_numeric($precio) || floatval($precio) <= 0) $errors[] = 'Precio inválido';
        if (!in_array($categoria, ['Pupusas', 'Bebidas', 'Complementos'])) $categoria = 'Pupusas';
        if (!in_array($estado, ['Activo', 'Agotado'])) $estado = 'Activo';
        $imgPath = null;
        $uploadRes = handleUpload('imagen', $uploadDir);
        if ($uploadRes && isset($uploadRes['error'])) $errors[] = $uploadRes['error'];
        elseif ($uploadRes && isset($uploadRes['path'])) $imgPath = $uploadRes['path'];
        if (!$imgPath) $imgPath = imagenPorDefecto($categoria);
        if (empty($errors)) {
            $ins = $pdo->prepare("INSERT INTO productos (nombre, descripcion, precio, categoria, estado, imagen) VALUES (?,?,?,?,?,?)");
            $ins->execute([
                $nombre,
                $descripcion ?: 'Sin descripción',
                number_format(floatval($precio), 2, '.', ''),
                $categoria,
                $estado,
                $imgPath,
            ]);
            flash('ok', 'Producto "' . $nombre . '" creado correctamente');
            header('Location: productos.php');
            exit;
        } else {
            flash('err', implode(' · ', $errors));
            $_SESSION['old'] = $_POST;
        }
    } elseif ($action === 'editar') {
        $id = intval($_POST['id'] ?? 0);
        $q = $pdo->prepare("SELECT * FROM productos WHERE id = ?");
        $q->execute([$id]);
        $actual = $q->fetch();
        if (!$actual) {
            flash('err', 'Producto no encontrado');
        } else {
            $nombre = trim($_POST['nombre'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $precio = trim($_POST['precio'] ?? '');
            $categoria = trim($_POST['categoria'] ?? 'Pupusas');
            $estado = trim($_POST['estado'] ?? 'Activo');
            $errors = [];
            if ($nombre === '' || strlen($nombre) < 3) $errors[] = 'Nombre requerido';
            if (!is_numeric($precio) || floatval($precio) <= 0) $errors[] = 'Precio inválido';
            if (!in_array($categoria, ['Pupusas', 'Bebidas', 'Complementos'])) $categoria = 'Pupusas';
            if (!in_array($estado, ['Activo', 'Agotado'])) $estado = 'Activo';
            $imgPath = $actual['imagen'];
            $uploadRes = handleUpload('imagen', $uploadDir);
            if ($uploadRes && isset($uploadRes['error'])) $errors[] = $uploadRes['error'];
            elseif ($uploadRes && isset($uploadRes['path'])) {
                if (strpos((string) $imgPath, 'assets/img/productos/') === 0 && file_exists(__DIR__ . '/../' . $imgPath)) @unlink(__DIR__ . '/../' . $imgPath);
                $imgPath = $uploadRes['path'];
            }
            if (empty($errors)) {
                $up = $pdo->prepare("UPDATE productos SET nombre=?, descripcion=?, precio=?, categoria=?, estado=?, imagen=? WHERE id=?");
                $up->execute([
                    $nombre,
                    $descripcion ?: 'Sin descripción',
                    number_format(floatval($precio), 2, '.', ''),
                    $categoria,
                    $estado,
                    $imgPath,
                    $id,
                ]);
                flash('ok', 'Producto "' . $nombre . '" actualizado correctamente');
                header('Location: productos.php');
                exit;
            } else {
                flash('err', implode(' · ', $errors));
                $_SESSION['old_edit'] = $_POST;
            }
        }
    }
}

// GET eliminar / toggle (protegidos por token CSRF)
// Volver al listado conservando búsqueda, filtro y página
$backProd = url_retorno($_GET['back'] ?? '', 'productos.php');
if (isset($_GET['del']) || isset($_GET['toggle'])) {
    if (!hash_equals(csrf_token(), (string) ($_GET['csrf'] ?? ''))) {
        flash('err', 'Enlace no válido — vuelve a intentar desde la página');
        header('Location: ' . $backProd);
        exit;
    }
}
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $q = $pdo->prepare("SELECT imagen FROM productos WHERE id = ?");
    $q->execute([$id]);
    $img = $q->fetchColumn();
    if ($img !== false) {
        if (strpos((string) $img, 'assets/img/productos/') === 0 && file_exists(__DIR__ . '/../' . $img)) @unlink(__DIR__ . '/../' . $img);
        // los ítems de pedidos conservan nombre/precio: solo se suelta la referencia
        $pdo->prepare("UPDATE pedido_items SET producto_id = NULL WHERE producto_id = ?")->execute([$id]);
        $pdo->prepare("DELETE FROM productos WHERE id = ?")->execute([$id]);
        flash('ok', 'Producto eliminado');
    } else {
        flash('err', 'Producto no encontrado');
    }
    header('Location: ' . $backProd);
    exit;
}
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);
    $q = $pdo->prepare("SELECT nombre, estado FROM productos WHERE id = ?");
    $q->execute([$id]);
    $prod = $q->fetch();
    if ($prod) {
        $nuevo = $prod['estado'] === 'Activo' ? 'Agotado' : 'Activo';
        $pdo->prepare("UPDATE productos SET estado = ? WHERE id = ?")->execute([$nuevo, $id]);
        // flash() solo acepta string: el toast viaja como JSON
        flash('toast', json_encode([
            'icon'  => $nuevo === 'Activo' ? 'success' : 'warning',
            'title' => $nuevo === 'Activo' ? 'Producto activado' : 'Producto desactivado',
            'text'  => $prod['nombre'],
        ], JSON_UNESCAPED_UNICODE));
    } else {
        flash('toast', json_encode([
            'icon'  => 'error',
            'title' => 'Producto no encontrado',
            'text'  => '',
        ], JSON_UNESCAPED_UNICODE));
    }
    header('Location: ' . $backProd);
    exit;
}

// — Filtros GET: búsqueda (q) y categoría —
$q = q_get();
$cat = in_array($_GET['cat'] ?? '', ['Pupusas', 'Bebidas', 'Complementos'], true) ? $_GET['cat'] : '';

$where = [];
$params = [];
if ($q !== '') {
    $like = like_param($q);
    $where[] = '(nombre LIKE ? OR descripcion LIKE ?)';
    $params[] = $like;
    $params[] = $like;
}
if ($cat !== '') {
    $where[] = 'categoria = ?';
    $params[] = $cat;
}
$sqlWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';

// Totales globales (encabezado)
$total = (int) $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
$activos = (int) $pdo->query("SELECT COUNT(*) FROM productos WHERE estado='Activo'")->fetchColumn();

// Coincidencias de la búsqueda por categoría (para los números de las píldoras)
$conteoCat = ['Todas' => 0, 'Pupusas' => 0, 'Bebidas' => 0, 'Complementos' => 0];
$sqlQ = $q !== '' ? ' WHERE (nombre LIKE ? OR descripcion LIKE ?)' : '';
$stmt = $pdo->prepare("SELECT categoria, COUNT(*) n FROM productos{$sqlQ} GROUP BY categoria");
$stmt->execute($q !== '' ? [like_param($q), like_param($q)] : []);
foreach ($stmt->fetchAll() as $r) {
    if (isset($conteoCat[$r['categoria']])) {
        $conteoCat[$r['categoria']] = (int) $r['n'];
    }
    $conteoCat['Todas'] += (int) $r['n'];
}

// Total filtrado + página actual
$stmt = $pdo->prepare("SELECT COUNT(*) FROM productos{$sqlWhere}");
$stmt->execute($params);
$totalFiltrado = (int) $stmt->fetchColumn();
$pg = paginar($totalFiltrado, 12, pagina_get());

// LIMIT/OFFSET son enteros de paginar(): seguros de interpolar
$stmt = $pdo->prepare("SELECT * FROM productos{$sqlWhere} ORDER BY id LIMIT {$pg['porPagina']} OFFSET {$pg['offset']}");
$stmt->execute($params);
$productos = $stmt->fetchAll();

$paginacion = [
    'base' => 'productos.php',
    'q' => $q,
    'params' => ['cat' => $cat],
    'total' => $totalFiltrado,
    'porPagina' => $pg['porPagina'],
    'pagina' => $pg['pagina'],
    'etiqueta' => 'productos',
    'placeholder' => 'Buscar por nombre o descripción…',
];
?><!doctype html>
<html lang="es">
<head>
<?php $page_title='Productos'; include __DIR__ . "/../modules/views/layouts/head-admin.php"; ?>
<link rel="stylesheet" href="<?= LINK_DROPZONE_CSS ?>" type="text/css">
</head>
<body class="admin-body">
<?php $admin_active='productos'; include __DIR__ . "/../modules/views/layouts/sidebar.php"; ?>
<div class="admin-main">
<?php $admin_title='Productos'; $admin_sub=$total.' productos · '.$activos.' activos'; include __DIR__ . "/../modules/views/layouts/header-admin.php"; ?>
<main class="admin-content"><?php include __DIR__ . "/../modules/views/admin/productos.php"; ?></main>
</div>
</body>
</html>
