<?php
/**
 * Fusión de las hojas de estilo del frontend en un solo archivo.
 *
 * Uso: php herramientas/fusionar_css.php
 *
 * Se ejecuta una vez para generar assets/css/estilos.css. Se queda en el repo
 * por si hay que rehacerlo, pero a partir de ese momento se edita estilos.css
 * directamente: este script es la receta, no la fuente de verdad.
 *
 * Que el CSS del proyecto sea un solo archivo es una decisión, no una casualidad:
 * el orden de las reglas queda escrito una vez y no depende de cuántas
 * etiquetas <link> se olviden en cada plantilla.
 */

$raiz = dirname(__DIR__);
$css  = $raiz . '/assets/css';

/**
 * Orden de concatenación. Es el mismo que tenía el proyecto cuando eran cuatro
 * archivos, elegido para que la cascada no cambie al unirlos:
 *
 *   1. custom.css  tokens (:root), base, cabecera, pie y el bloque px- de las
 *                  páginas públicas. Primero porque todo lo demás usa sus
 *                  variables.
 *   2. landing.css lenguaje visual de la portada (.nl-*). Detrás de custom.css
 *                  como estaba: las reglas px- que lo pisan llevan un ancestro
 *                  .px-* y ganan por especificidad, no por orden.
 *   3. admin.css   panel. No toca selectores globales, así que no molesta a la
 *                  web pública.
 *   4. pos.css     punto de venta, al final como estaba.
 */
$orden = [
    'custom.css'  => 'BASE    tokens, cabecera, pie y páginas públicas',
    'landing.css' => 'LANDING lenguaje visual de la portada (.nl-*)',
    'admin.css'   => 'PANEL   administración (.admin-*)',
    'pos.css'     => 'POS     punto de venta (.pos-*)',
];

$salida = [];
$salida[] = '/* ============================================================================';
$salida[] = '   Pupusería Rinas · hoja de estilos única';
$salida[] = '   ----------------------------------------------------------------------------';
$salida[] = '   Un solo archivo para todo el frontend, en cuatro bloques:';
$salida[] = '';
$salida[] = '     1. BASE    tokens (:root), cabecera, pie y páginas públicas';
$salida[] = '     2. LANDING lenguaje visual de la portada (.nl-*)';
$salida[] = '     3. PANEL   administración (.admin-*)';
$salida[] = '     4. POS     punto de venta (.pos-*)';
$salida[] = '';
$salida[] = '   El orden importa. BASE va primero porque el resto usa sus variables';
$salida[] = '   (--rinas-azul, --rinas-crema, --font-titulos). LANDING va después de';
$salida[] = '   BASE, igual que cuando eran archivos separados: las reglas px- que lo';
$salida[] = '   pisan llevan un ancestro .px- y ganan por especificidad. Dentro de';
$salida[] = '   BASE, el bloque px- de las páginas públicas va al final a propósito.';
$salida[] = '';
$salida[] = '   Se versiona con LINK_CSS_VERSION (php/enlaces.php).';
$salida[] = '   ========================================================================== */';

foreach ($orden as $archivo => $titulo) {
    $ruta = $css . '/' . $archivo;
    if (!is_file($ruta)) {
        fwrite(STDERR, "Falta el archivo de origen: $archivo\n");
        exit(1);
    }
    $salida[] = '';
    $salida[] = '/* ============================================================================';
    $salida[] = '   ' . $titulo;
    $salida[] = '   ========================================================================== */';
    $salida[] = rtrim(file_get_contents($ruta));
}

$destino = $css . '/estilos.css';
file_put_contents($destino, implode("\n", $salida) . "\n");

printf("estilos.css escrito: %s bytes\n", number_format(filesize($destino)));