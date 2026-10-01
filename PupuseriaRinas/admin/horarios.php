<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/auth.php';
requerir_login();

$pdo = db();
$flash_ok = leer_flash('ok');
$flash_err = leer_flash('err');

$dias = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];
$campos = ['am_apertura', 'am_cierre', 'pm_apertura', 'pm_cierre'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'guardar') {
    verificar_csrf();
    $h = $_POST['h'] ?? [];
    $errors = [];
    $pendientes = [];
    foreach ($dias as $d => $nombre) {
        $row = $h[$d] ?? [];
        $vals = [];
        foreach ($campos as $k) {
            $v = trim($row[$k] ?? '');
            if ($v === '') {
                $vals[$k] = null;
            } elseif (preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $v)) {
                $vals[$k] = $v . ':00';
            } else {
                $errors[] = "Hora inválida en {$nombre}";
                $vals[$k] = null;
            }
        }
        if ($vals['am_apertura'] && $vals['am_cierre'] && $vals['am_apertura'] >= $vals['am_cierre']) {
            $errors[] = "Turno de la mañana mal ordenado en {$nombre}";
        }
        if ($vals['pm_apertura'] && $vals['pm_cierre'] && $vals['pm_apertura'] >= $vals['pm_cierre']) {
            $errors[] = "Turno de la tarde mal ordenado en {$nombre}";
        }
        $pendientes[$d] = [$vals, !empty($row['cerrado']) ? 1 : 0];
    }
    // Solo se guarda si todo el formulario es válido
    if (!$errors) {
        $up = $pdo->prepare("UPDATE horarios SET am_apertura = ?, am_cierre = ?, pm_apertura = ?, pm_cierre = ?, cerrado = ? WHERE dia = ?");
        foreach ($pendientes as $d => [$vals, $cerrado]) {
            $up->execute([$vals['am_apertura'], $vals['am_cierre'], $vals['pm_apertura'], $vals['pm_cierre'], $cerrado, $d]);
        }
        flash('ok', 'Horarios guardados correctamente');
    } else {
        flash('err', implode(' · ', $errors));
    }
    header('Location: horarios.php');
    exit;
}

// — Datos (asegura las 7 filas de la semana) —
$pdo->exec("INSERT IGNORE INTO horarios (dia) VALUES (1),(2),(3),(4),(5),(6),(7)");
$horarios = [];
foreach ($pdo->query("SELECT * FROM horarios ORDER BY dia") as $r) {
    $horarios[(int) $r['dia']] = $r;
}
$hayAlgo = false;
foreach ($horarios as $r) {
    if (!empty($r['cerrado']) || rango_dia($r) !== '') {
        $hayAlgo = true;
        break;
    }
}
$diaHoy = (int) date('N');
?><!doctype html>
<html lang="es">
<head>
<?php $page_title='Horarios'; include __DIR__ . "/../modules/views/layouts/head-admin.php"; ?>
</head>
<body class="admin-body">
<?php $admin_active='horarios'; include __DIR__ . "/../modules/views/layouts/sidebar.php"; ?>
<div class="admin-main">
<?php $admin_title='Horarios'; $admin_sub='Semana actual · se refleja en Ubicación'; include __DIR__ . "/../modules/views/layouts/header-admin.php"; ?>
<main class="admin-content"><?php include __DIR__ . "/../modules/views/admin/horarios.php"; ?></main>
</div>
</body>
</html>
