<?php
require_once __DIR__ . '/../app/inicio.php';
require_once __DIR__ . '/../app/sesion.php';
requerir_login();

$pdo = db();
// El éxito viaja como toast (SweetAlert), no como alerta dentro de la página:
// mismo criterio que en productos y pedidos. Los errores siguen llegando como
// flash 'err' porque pueden ser varios y necesitan más lectura.
$flash_toast = json_decode((string) leer_flash('toast'), true) ?: null;
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
                    // Se borran los logos anteriores: si cambiaste de formato
                    // (jpg -> png) el archivo viejo se quedaba ocupando disco y
                    // apuntando a una imagen que ya no usas.
                    foreach ((array) glob($dir . '/logo_config.*') as $previo) {
                        if (is_file($previo) && basename($previo) !== $name) {
                            @unlink($previo);
                        }
                    }
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
        flash('toast', json_encode(['icon' => 'success', 'title' => 'Configuración guardada'], JSON_UNESCAPED_UNICODE));
        header('Location: config.php');
        exit;
    }
    $flash_err = implode(' · ', $errors);
}

// Guardado automático de la ubicación (botón "Estoy aquí" en el preview del
// mapa): recibe las coordenadas del navegador, las valida y las persiste en
// maps_query sin recargar la página. Devuelve JSON.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'guardar_ubicacion') {
    verificar_csrf();
    header('Content-Type: application/json; charset=utf-8');
    $coords = trim($_POST['maps_query'] ?? '');
    if (!preg_match('/^-?\d{1,3}(?:\.\d+)?\s*,\s*-?\d{1,3}(?:\.\d+)?$/', $coords)) {
        http_response_code(422);
        echo json_encode(['ok' => false, 'error' => 'Coordenadas inválidas']);
        exit;
    }
    $upUbic = $pdo->prepare("INSERT INTO configuracion (clave, valor) VALUES (?,?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)");
    $upUbic->execute(['maps_query', $coords]);
    echo json_encode(['ok' => true, 'maps_query' => $coords]);
    exit;
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
<?php $page_title='Configuración'; include __DIR__ . "/../vistas/plantillas/admin_cabeza.php"; ?>
</head>
<body class="admin-body">
<?php $admin_active='config'; include __DIR__ . "/../vistas/plantillas/admin_barra.php"; ?>
<div class="admin-main">
<?php $admin_title='Configuración'; $admin_sub=$hayConfig ? 'Datos del negocio guardados' : 'Sin configurar aún'; include __DIR__ . "/../vistas/plantillas/admin_cabecera.php"; ?>
<main class="admin-content"><?php include __DIR__ . "/../vistas/admin/config.php"; ?></main>
</div>
</body>
</html>
