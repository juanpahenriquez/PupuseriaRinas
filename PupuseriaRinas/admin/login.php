<?php
require_once __DIR__ . '/../config/conexion.php';
require_once __DIR__ . '/../config/auth.php';

if (usuario_actual()) {
    header('Location: index.php');
    exit;
}

$error = null;
$loginOk = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verificar_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if ($email === '' || $password === '') {
        $error = 'Escribe correo y contraseña.';
    } elseif (login($email, $password)) {
        $loginOk = true;
    } else {
        $error = 'Credenciales incorrectas o usuario inactivo.';
    }
}
?><!doctype html>
<html lang="es">
<head>
<?php $page_title = 'Login'; include __DIR__ . "/../modules/views/layouts/head-admin.php"; ?>
<link rel="stylesheet" href="<?= LINK_SWEETALERT_CSS ?>">
<script src="<?= LINK_SWEETALERT_JS ?>"></script>
</head>
<body>
<?php include __DIR__ . "/../modules/views/admin/login.php"; ?>
</body>
</html>
