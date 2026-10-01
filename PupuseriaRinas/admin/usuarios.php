<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/auth.php';
requerir_login();

$pdo = db();
$yo = usuario_actual();
$flash_ok = leer_flash('ok');
$flash_err = leer_flash('err');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $action = $_POST['action'] ?? '';
    // Volver a la lista conservando búsqueda, filtro y página
    $back = url_retorno($_POST['back'] ?? '', 'usuarios.php');

    if ($action === 'crear') {
        $nombre = trim($_POST['nombre'] ?? '');
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $rol = in_array($_POST['rol'] ?? '', ['admin', 'cajero'], true) ? $_POST['rol'] : 'cajero';
        $errors = [];
        if ($nombre === '' || mb_strlen($nombre) < 3) {
            $errors[] = 'Nombre requerido (mín 3 caracteres)';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Correo inválido';
        }
        if (strlen($password) < 6) {
            $errors[] = 'Contraseña mínimo 6 caracteres';
        }
        if (!$errors) {
            $exists = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
            $exists->execute([$email]);
            if ((int) $exists->fetchColumn() > 0) {
                $errors[] = 'Ese correo ya está registrado';
            }
        }
        if (!$errors) {
            $ins = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol, activo) VALUES (?,?,?,?,1)");
            $ins->execute([$nombre, $email, password_hash($password, PASSWORD_DEFAULT), $rol]);
            flash('ok', 'Usuario "' . $nombre . '" creado correctamente');
            header('Location: ' . $back);
            exit;
        }
        $flash_err = implode(' · ', $errors);
    } elseif ($action === 'eliminar') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === (int) $yo['id']) {
            $flash_err = 'No puedes eliminar tu propia cuenta';
        } elseif ($id > 0) {
            $u = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
            $u->execute([$id]);
            $rol = $u->fetchColumn();
            if ($rol === 'admin') {
                $admins = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='admin' AND activo=1")->fetchColumn();
                if ($admins <= 1) {
                    $flash_err = 'No se puede eliminar el único admin activo';
                }
            }
            if ($flash_err === null) {
                $pdo->prepare("DELETE FROM usuarios WHERE id = ?")->execute([$id]);
                flash('ok', 'Usuario eliminado');
                header('Location: ' . $back);
                exit;
            }
        }
    } elseif ($action === 'toggle') {
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === (int) $yo['id']) {
            $flash_err = 'No puedes desactivar tu propia cuenta';
        } elseif ($id > 0) {
            $pdo->prepare("UPDATE usuarios SET activo = 1 - activo WHERE id = ?")->execute([$id]);
            flash('ok', 'Estado del usuario actualizado');
            header('Location: ' . $back);
            exit;
        }
    } elseif ($action === 'rol') {
        $id = (int) ($_POST['id'] ?? 0);
        $rol = $_POST['rol'] ?? '';
        if ($id === (int) $yo['id']) {
            $flash_err = 'No puedes cambiar tu propio rol aquí';
        } elseif ($id > 0 && in_array($rol, ['admin', 'cajero'], true)) {
            $u = $pdo->prepare("SELECT rol FROM usuarios WHERE id = ?");
            $u->execute([$id]);
            $rolActual = $u->fetchColumn();
            if ($rolActual === 'admin' && $rol === 'cajero') {
                $admins = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='admin' AND activo=1")->fetchColumn();
                if ($admins <= 1) {
                    $flash_err = 'No se puede degradar al único admin activo';
                }
            }
            if ($flash_err === null) {
                $pdo->prepare("UPDATE usuarios SET rol = ? WHERE id = ?")->execute([$rol, $id]);
                flash('ok', 'Rol actualizado');
                header('Location: ' . $back);
                exit;
            }
        } else {
            $flash_err = 'Rol no válido';
        }
    }
}

// — Filtros GET: búsqueda (q) y rol —
$q = q_get();
$fRol = in_array($_GET['rol'] ?? '', ['admin', 'cajero'], true) ? $_GET['rol'] : '';

$where = [];
$params = [];
if ($q !== '') {
    $where[] = '(nombre LIKE ? OR email LIKE ?)';
    $params[] = like_param($q);
    $params[] = like_param($q);
}
if ($fRol !== '') {
    $where[] = 'rol = ?';
    $params[] = $fRol;
}
$sqlWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';

// Totales globales (encabezado y píldoras)
$total = (int) $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$admins = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='admin'")->fetchColumn();
$cajeros = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='cajero'")->fetchColumn();

// Coincidencias de la búsqueda por rol (para los números de las píldoras)
$conteoRol = ['todos' => 0, 'admin' => 0, 'cajero' => 0];
$sqlQ = $q !== '' ? ' WHERE (nombre LIKE ? OR email LIKE ?)' : '';
$stmt = $pdo->prepare("SELECT rol, COUNT(*) n FROM usuarios{$sqlQ} GROUP BY rol");
$stmt->execute($q !== '' ? [like_param($q), like_param($q)] : []);
foreach ($stmt->fetchAll() as $r) {
    $conteoRol[$r['rol']] = (int) $r['n'];
    $conteoRol['todos'] += (int) $r['n'];
}

// Total filtrado + página actual
$stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios{$sqlWhere}");
$stmt->execute($params);
$totalFiltrado = (int) $stmt->fetchColumn();
$pg = paginar($totalFiltrado, 10, pagina_get());

// LIMIT/OFFSET son enteros de paginar(): seguros de interpolar
$stmt = $pdo->prepare("SELECT id, nombre, email, rol, activo, creado FROM usuarios{$sqlWhere}
                       ORDER BY rol DESC, nombre LIMIT {$pg['porPagina']} OFFSET {$pg['offset']}");
$stmt->execute($params);
$usuarios = $stmt->fetchAll();

$paginacion = [
    'base' => 'usuarios.php',
    'q' => $q,
    'params' => ['rol' => $fRol],
    'total' => $totalFiltrado,
    'porPagina' => $pg['porPagina'],
    'pagina' => $pg['pagina'],
    'etiqueta' => 'usuarios',
    'placeholder' => 'Buscar por nombre o correo…',
];
?><!doctype html>
<html lang="es">
<head>
<?php $page_title='Usuarios'; include __DIR__ . "/../modules/views/layouts/head-admin.php"; ?>
</head>
<body class="admin-body">
<?php $admin_active='usuarios'; include __DIR__ . "/../modules/views/layouts/sidebar.php"; ?>
<div class="admin-main">
<?php $admin_title='Usuarios'; $admin_sub=$total . ' cuentas · ' . $admins . ' admin · ' . $cajeros . ' cajeros'; include __DIR__ . "/../modules/views/layouts/header-admin.php"; ?>
<main class="admin-content"><?php include __DIR__ . "/../modules/views/admin/usuarios.php"; ?></main>
</div>
</body>
</html>
