<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/auth.php';
requerir_login();

$pdo = db();
$flash_ok = leer_flash('ok');
$flash_err = leer_flash('err');

$claves = ['direccion', 'maps_query', 'telefono', 'whatsapp', 'logo'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'guardar') {
    verificar_csrf();
    $direccion = trim($_POST['direccion'] ?? '');
    $maps_query = trim($_POST['maps_query'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $whatsapp = preg_replace('/\D/', '', $_POST['whatsapp'] ?? '');
    $errors = [];
    if ($telefono !== '' && !preg_match('/^[0-9\-\s+]{7,20}$/', $telefono)) {
        $errors[] = 'Teléfono inválido';
    }
    if ($whatsapp !== '' && strlen($whatsapp) < 8) {
        $errors[] = 'WhatsApp inválido (solo dígitos)';
    }

    // Subida opcional de logo
    $logoPath = null;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] !== UPLOAD_ERR_NO_FILE) {
        $f = $_FILES['logo'];
        if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Error al subir el logo (máx 2MB)';
        } else {
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'svg'], true)) {
                $errors[] = 'Formato de logo no permitido';
            } else {
                $dir = __DIR__ . '/../assets/img';
                $name = 'logo_config.' . ($ext === 'jpeg' ? 'jpg' : $ext);
                if (move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
                    $logoPath = 'assets/img/' . $name;
                } else {
                    $errors[] = 'No se pudo guardar el logo';
                }
            }
        }
    }

    if (!$errors) {
        $up = $pdo->prepare("INSERT INTO configuracion (clave, valor) VALUES (?,?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
        $up->execute(['direccion', $direccion]);
        $up->execute(['maps_query', $maps_query !== '' ? $maps_query : $direccion]);
        $up->execute(['telefono', $telefono]);
        $up->execute(['whatsapp', $whatsapp]);
        if ($logoPath !== null) {
            $up->execute(['logo', $logoPath]);
        }
        flash('ok', 'Configuración guardada');
        header('Location: config.php');
        exit;
    }
    $flash_err = implode(' · ', $errors);
}

$config = array_fill_keys($claves, '');
foreach ($pdo->query("SELECT clave, valor FROM configuracion") as $r) {
    if (array_key_exists($r['clave'], $config)) {
        $config[$r['clave']] = (string) $r['valor'];
    }
}
$hayConfig = array_filter($config, fn($v) => $v !== '');
?><!doctype html>
<html lang="es">
<head>
<?php $page_title='Configuración'; include __DIR__ . "/../modules/views/layouts/head-admin.php"; ?>
</head>
<body class="admin-body">
<?php $admin_active='config'; include __DIR__ . "/../modules/views/layouts/sidebar.php"; ?>
<div class="admin-main">
<?php $admin_title='Configuración'; $admin_sub=$hayConfig ? 'Datos del negocio guardados' : 'Sin configurar aún'; include __DIR__ . "/../modules/views/layouts/header-admin.php"; ?>
<main class="admin-content"><?php include __DIR__ . "/../modules/views/admin/config.php"; ?></main>
</div>
</body>
</html>
