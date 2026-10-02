<?php
// Prueba de los parciales compartidos fuera de pedidos: comprueba que
// $pieContador = false (productos/usuarios) deja el marcado exactamente como
// antes, y que = true mueve el contador al pie. Borrar al terminar.
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/util.php';

$paginacion = [
    'base' => 'productos.php', 'q' => '', 'params' => ['cat' => 'Pupusas'], 'total' => 47,
    'porPagina' => 12, 'pagina' => 3, 'etiqueta' => 'productos', 'placeholder' => 'Buscar…',
];

ob_start();
include __DIR__ . '/../modules/views/admin/_buscador.php';
include __DIR__ . '/../modules/views/admin/_paginacion.php';
$sinPie = ob_get_clean();

$sinPieInfoArriba  = (bool) preg_match('/<p class="admin-buscador-info/', $sinPie);
$sinPiePie         = (bool) preg_match('/admin-paginacion-pie/', $sinPie);
$sinPieNavCentrado = (bool) preg_match('/<nav class="admin-paginacion"/', $sinPie);

$pieContador = true;
ob_start();
include __DIR__ . '/../modules/views/admin/_buscador.php';
include __DIR__ . '/../modules/views/admin/_paginacion.php';
$conPie = ob_get_clean();

$conPieInfoArriba = (bool) preg_match('/<p class="admin-buscador-info/', $conPie);
$conPiePie        = (bool) preg_match('/admin-paginacion-pie/', $conPie);
$conPieResumen    = (bool) preg_match('/admin-paginacion-resumen/', $conPie);
$conPieNav        = (bool) preg_match('/<nav class="admin-paginacion"/', $conPie);
$conPieResumenTxt = '';
if (preg_match('/<p class="admin-paginacion-resumen">(.*?)<\/p>/s', $conPie, $m)) {
    $conPieResumenTxt = trim(html_entity_decode(strip_tags($m[1]), ENT_QUOTES, 'UTF-8'));
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'sin_pie' => [
        'contador_arriba' => $sinPieInfoArriba,
        'pie_presente'    => $sinPiePie,
        'nav_presente'    => $sinPieNavCentrado,
        'esperado'        => 'contador arriba=SI, pie=NO, nav=SI',
        'ok'              => $sinPieInfoArriba && !$sinPiePie && $sinPieNavCentrado,
    ],
    'con_pie' => [
        'contador_arriba' => $conPieInfoArriba,
        'pie_presente'    => $conPiePie,
        'resumen_presente'=> $conPieResumen,
        'nav_presente'    => $conPieNav,
        'resumen_texto'   => $conPieResumenTxt,
        'esperado'        => 'contador arriba=NO, pie=SI, nav=SI',
        'ok'              => !$conPieInfoArriba && $conPiePie && $conPieResumen && $conPieNav,
    ],
    'resumen_esperado' => 'Mostrando 25–36 de 47 productos · página 3 de 4',
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
