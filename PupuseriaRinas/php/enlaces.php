<?php
/**
 * Enlaces externos centralizados (CDN) + <head> público.
 * Constantes: LINK_BOOTSTRAP_CSS, LINK_BOOTSTRAP_JS, LINK_SWEETALERT_CSS,
 *             LINK_SWEETALERT_JS, LINK_APEXCHARTS_JS.
 */

define('LINK_BOOTSTRAP_VERSION', '5.3.3');
define('LINK_BOOTSTRAP', 'https://cdn.jsdelivr.net/npm/bootstrap@' . LINK_BOOTSTRAP_VERSION . '/dist');
define('LINK_BOOTSTRAP_CSS', LINK_BOOTSTRAP . '/css/bootstrap.min.css');
define('LINK_BOOTSTRAP_JS', LINK_BOOTSTRAP . '/js/bootstrap.bundle.min.js');

define('LINK_SWEETALERT_VERSION', '11.22.4');
define('LINK_SWEETALERT', 'https://cdn.jsdelivr.net/npm/sweetalert2@' . LINK_SWEETALERT_VERSION . '/dist');
define('LINK_SWEETALERT_CSS', LINK_SWEETALERT . '/sweetalert2.min.css');
define('LINK_SWEETALERT_JS', LINK_SWEETALERT . '/sweetalert2.all.min.js');

define('LINK_APEXCHARTS_VERSION', '3.52.0');
define('LINK_APEXCHARTS_JS', 'https://cdn.jsdelivr.net/npm/apexcharts@' . LINK_APEXCHARTS_VERSION . '/dist/apexcharts.min.js');

define('LINK_FONTAWESOME_VERSION', '6.5.2');
define('LINK_FONTAWESOME_CSS', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/' . LINK_FONTAWESOME_VERSION . '/css/all.min.css');

define('LINK_DROPZONE_VERSION', '5');
define('LINK_DROPZONE', 'https://unpkg.com/dropzone@' . LINK_DROPZONE_VERSION . '/dist/min');
define('LINK_DROPZONE_CSS', LINK_DROPZONE . '/dropzone.min.css');
define('LINK_DROPZONE_JS', LINK_DROPZONE . '/dropzone.min.js');

// Versión única del CSS propio (público + admin) para romper cachés al desplegar
define('LINK_CSS_VERSION', '33');

if (!function_exists('rinas_head_publico')) {
    function rinas_head_publico(string $customCss = 'assets/css/custom.css?v=' . LINK_CSS_VERSION): void
    {
        ?>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="<?= LINK_BOOTSTRAP_CSS ?>" rel="stylesheet">
        <link href="<?= $customCss ?>" rel="stylesheet">
        <?php
    }
}
