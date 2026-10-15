<?php
/**
 * Arranque de la aplicación.
 *
 * Un único punto de entrada al núcleo: cualquier página del proyecto empieza por
 * aquí, así que el núcleo se carga una sola vez (require_once) y en el mismo
 * orden sin importar desde dónde se entre.
 *
 * Qué trae:
 *   app/enlaces.php    → constantes de CDN (Bootstrap, SweetAlert, Splide...)
 *                       y rinas_head_publico() para las páginas públicas
 *   app/bd.php         → conexión PDO, esquema y config_valor()
 *   app/utilerias.php  → helpers de formato, paginación y URLs
 *
 * NO carga app/sesion.php: la sesión solo la necesitan el panel y las páginas
 * que muestran avisos de login. Quien la necesite la carga explícitamente.
 *
 * Uso:
 *     require_once __DIR__ . '/app/inicio.php';
 */

/** Ruta raíz del proyecto, para enlazar a archivos desde cualquier nivel. */
define('RUTA_BASE', dirname(__DIR__));

require_once __DIR__ . '/enlaces.php';
require_once __DIR__ . '/utilerias.php';
require_once __DIR__ . '/bd.php';