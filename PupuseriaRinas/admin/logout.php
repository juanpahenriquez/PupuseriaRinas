<?php
require_once __DIR__ . '/../app/inicio.php';
require_once __DIR__ . '/../app/sesion.php';
logout();
header('Location: login.php');
exit;
