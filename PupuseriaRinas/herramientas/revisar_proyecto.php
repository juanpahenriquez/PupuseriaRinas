<?php
/**
 * Comprobación de integridad del proyecto.
 * Uso: php herramientas/revisar_proyecto.php
 *
 * Revisa, en todo el proyecto:
 *   1. Cada include/require con __DIR__ apunta a un archivo que existe.
 *   2. Cada <link>/<script>/<img> relativo apunta a un archivo real.
 *   3. Cada href a otra página .php apunta a una página real.
 *
 * Detalle importante: las URLs de una vista NO se resuelven desde el archivo
 * que las contiene, sino desde la URL del navegador que la está pintando. Una
 * vista en vistas/admin/ se incluye desde admin/pedidos.php, así que un
 * "../assets/img/logo.jpg" therein apunta a /assets/img/logo.jpg y no a
 * modules/views/assets/... Por eso cada archivo se asocia al contexto desde
 * el que se sirve y se resuelve contra esa base.
 *
 * Sale con código 1 si encuentra algún fallo.
 */

$raiz = dirname(__DIR__);
$fallos = [];
$avisos = [];

/* Contexto de URL desde el que se sirve cada archivo. */
function contexto_de(string $rutaRelativa): string
{
    if (preg_match('#^(admin/|vistas/admin/|vistas/plantillas/admin_)#', $rutaRelativa)) {
        return 'admin/';
    }
    return '';
}

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($raiz));
$phpFiles = [];
foreach ($iterator as $f) {
    if ($f->isFile() && strtolower($f->getExtension()) === 'php') {
        $phpFiles[] = $f->getPathname();
    }
}

foreach ($phpFiles as $archivo) {
    /* Contexto y rutas siempre con "/": en Windows str_replace deja "\", que
       hace que los patrones con "/" no encajen nunca. */
    $rel = str_replace('\\', '/', str_replace($raiz . DIRECTORY_SEPARATOR, '', $archivo));
    $txt = file_get_contents($archivo);
    $ctx = contexto_de($rel);

    /* Los ejemplos de los comentarios no son includes reales: se limpian
       antes de escanear, o el propio informe se cuela en sus propios fallos. */
    $codigo = preg_replace('#/\*.*?\*/#s', '', $txt);
    $codigo = preg_replace('#(^|\s)//.*$#m', '$1', $codigo);

    /* --- 1. includes --- */
    if (preg_match_all('/__DIR__\s*\.\s*([\'"])(.+?)\1/', $codigo, $m, PREG_SET_ORDER)) {
        foreach ($m as $x) {
            $destino = realpath(dirname($archivo) . DIRECTORY_SEPARATOR . $x[2]);
            if ($destino === false) {
                $fallos[] = "include roto · $rel → {$x[2]}";
            }
        }
    }

    /* --- 2 y 3. enlaces del marcado --- */
    if (preg_match_all('/(?:src|href|action)=[\'"](?!https?:|#|mailto:|tel:|data:|javascript:)([^\'"]+)[\'"]/', $codigo, $m, PREG_SET_ORDER)) {
        foreach ($m as $x) {
            $url = html_entity_decode(trim($x[1]));
            if ($url === '' || substr($url, -1) === '/' || str_starts_with($url, '<?')) {
                continue;
            }
            $limpio = preg_replace('/[?#].*$/', '', $url);

            /* assets/ siempre cuelga de la raíz del sitio, aunque la vista
               escriba "../assets/...". */
            if (preg_match('#(^|/)assets/#', $limpio)) {
                $limpio = preg_replace('#^(\.\./)+#', '', $limpio);
                $destino = $raiz . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $limpio);
            } else {
                /* Se apila el camino como lo haría el navegador, con ".."
                   retrocediendo un nivel y "../.." saliendo del sitio. */
                $partes = array_filter(explode('/', $ctx . $limpio), fn($s) => $s !== '' && $s !== '.');
                $pila = [];
                foreach ($partes as $parte) {
                    if ($parte === '..') {
                        array_pop($pila);
                    } else {
                        $pila[] = $parte;
                    }
                }
                $destino = $raiz . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $pila);
            }

            if (file_exists($destino)) {
                continue;
            }
            if (preg_match('/\.php$/', $limpio)) {
                $fallos[] = "página enlazada que no existe · $rel → $limpio";
            } else {
                $avisos[] = "recurso no encontrado · $rel → $limpio";
            }
        }
    }
}

echo "Archivos PHP revisados: " . count($phpFiles) . "\n\n";

if ($fallos) {
    echo "FALLOS (" . count($fallos) . "):\n";
    foreach (array_unique($fallos) as $f) {
        echo "  - $f\n";
    }
    echo "\n";
} else {
    echo "OK · todos los includes y páginas enlazadas resuelven.\n\n";
}

if ($avisos) {
    echo "Avisos (" . count(array_unique($avisos)) . "):\n";
    foreach (array_unique($avisos) as $a) {
        echo "  - $a\n";
    }
}

exit($fallos ? 1 : 0);