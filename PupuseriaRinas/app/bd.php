<?php
/**
 * Núcleo de datos · conexión PDO a MySQL (WAMP: root / sin contraseña).
 *
 * Crea la base, las tablas y los datos mínimos la primera vez que se usa, de
 * forma idempotente: todo son CREATE TABLE IF NOT EXISTS e INSERT IGNORE, así
 * que volver a pasar por aquí no pisa lo que ya haya escrito el panel.
 *
 * Para leer la configuración del negocio:
 *     $pdo = db();
 *     $direccion = config_valor('direccion', 'Paseo General Escalón #123');
 *
 * En las vistas públicas se usa db(false): si MySQL está apagado la página
 * sigue entrando y muestra los textos por defecto, en vez de romperse.
 */
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'pupuseria_rinas');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

require_once __DIR__ . '/utilerias.php';

/**
 * Conexión PDO a MySQL. Crea la base y el esquema si no existen.
 *
 * @param bool $fatale Si true y MySQL no responde, muestra un error y detiene
 *                     la página. Si false devuelve null y sigue.
 * @return PDO|null
 */
function db(bool $fatale = true): ?PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $opciones = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        /* Todo el proyecto recorre las consultas como foreach ($consulta as $fila)
           y accede con $fila['clave'], así que el modo por defecto es el
           asociativo. Si se quita, admin y vistas públicas dejan de leer datos. */
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        /* Primero sin base: es la que hace posible crearla. */
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET,
            DB_USER,
            DB_PASS,
            $opciones
        );
        $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` DEFAULT CHARACTER SET ' . DB_CHARSET);
        $pdo->exec('USE `' . DB_NAME . '`');
        instalar_esquema($pdo);
        return $pdo;
    } catch (Throwable $e) {
        $pdo = null;
        if (!$fatale) {
            return null;
        }
        http_response_code(500);
        echo '<h1>No pudimos conectar con la base de datos</h1>';
        echo '<p>Revisa que MySQL esté encendido en WampServer.</p>';
        /* El detalle del error solo se muestra si el núcleo se ha cargado con
           RUTA_BASE definida: el mensaje crudo puede traer rutas del servidor. */
        if (defined('RUTA_BASE')) {
            echo '<pre style="white-space:pre-wrap;font-size:12px">' . htmlspecialchars($e->getMessage()) . '</pre>';
        }
        exit;
    }
}

/**
 * Lee un valor de la tabla `configuracion` (dirección, teléfono, logo...).
 *
 * @param string $clave    Clave a buscar.
 * @param string $defecto  Valor a devolver si no existe o si MySQL falla.
 */
function config_valor(string $clave, string $defecto = ''): string
{
    try {
        $pdo = db(false);
        if (!$pdo) {
            return $defecto;
        }
        $stmt = $pdo->prepare('SELECT valor FROM configuracion WHERE clave = ? LIMIT 1');
        $stmt->execute([$clave]);
        $valor = $stmt->fetchColumn();
        return ($valor === false || $valor === null || $valor === '') ? $defecto : (string) $valor;
    } catch (Throwable $e) {
        return $defecto;
    }
}

/**
 * Esquema y datos mínimos. Idempotente: se puede llamar en cada conexión.
 */
function instalar_esquema(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL,
            email VARCHAR(150) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            rol ENUM('admin','cajero') NOT NULL DEFAULT 'cajero',
            activo TINYINT(1) NOT NULL DEFAULT 1,
            creado DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . "
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS productos (
            id INT NOT NULL AUTO_INCREMENT,
            nombre VARCHAR(120) NOT NULL,
            descripcion VARCHAR(255) NOT NULL DEFAULT 'Sin descripción',
            precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            categoria ENUM('Pupusas','Bebidas','Complementos') NOT NULL DEFAULT 'Pupusas',
            estado ENUM('Activo','Agotado') NOT NULL DEFAULT 'Activo',
            imagen VARCHAR(255) DEFAULT NULL,
            creado DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . "
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pedidos (
            id INT NOT NULL AUTO_INCREMENT,
            folio VARCHAR(20) NOT NULL DEFAULT '',
            tipo ENUM('llevar','recoger') NOT NULL DEFAULT 'recoger',
            cliente VARCHAR(120) NOT NULL DEFAULT '',
            telefono VARCHAR(30) NOT NULL DEFAULT '',
            notas VARCHAR(255) NOT NULL DEFAULT '',
            total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            estado ENUM('pendiente','cocina','listo','entregado') NOT NULL DEFAULT 'pendiente',
            creado DATETIME DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY estado (estado),
            KEY creado (creado)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . "
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pedido_items (
            id INT NOT NULL AUTO_INCREMENT,
            pedido_id INT NOT NULL,
            producto_id INT DEFAULT NULL,
            nombre VARCHAR(120) NOT NULL,
            cantidad INT NOT NULL DEFAULT 1,
            precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            PRIMARY KEY (id),
            KEY pedido_id (pedido_id),
            CONSTRAINT pedido_items_ibfk_1 FOREIGN KEY (pedido_id)
                REFERENCES pedidos (id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . "
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS horarios (
            dia TINYINT UNSIGNED NOT NULL COMMENT '1=Lunes .. 7=Domingo',
            am_apertura TIME DEFAULT NULL,
            am_cierre TIME DEFAULT NULL,
            pm_apertura TIME DEFAULT NULL,
            pm_cierre TIME DEFAULT NULL,
            cerrado TINYINT(1) NOT NULL DEFAULT 0,
            PRIMARY KEY (dia)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . "
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS configuracion (
            clave VARCHAR(60) NOT NULL,
            valor TEXT,
            PRIMARY KEY (clave)
        ) ENGINE=InnoDB DEFAULT CHARSET=" . DB_CHARSET . "
    ");

    /* ---- Datos mínimos ---- */

    // Cuenta de arranque. El hash es el de la instalación original: es la
    // contraseña que ya conoces. En una instalación nueva, cámbiala desde el
    // panel nada más entrar (Usuarios).
    $pdo->exec("
        INSERT IGNORE INTO usuarios (nombre, email, password_hash, rol, activo)
        VALUES ('Admin Rinas', 'admin@rinas.com',
                '\$2y\$10\$.ezbPyPcc2ERDxBF1aMokOoyu2O7p4rPX9I8i5zEY7mT9q6Tb.6Ay',
                'admin', 1)
    ");

    // Textos por defecto del negocio. INSERT IGNORE no pisa lo que el panel
    // haya guardado ya.
    $cfg = [
        'direccion'   => 'Paseo General Escalón #123, San Salvador',
        'telefono'    => '7000-0000',
        'whatsapp'    => '50370000000',
        'maps_query'  => 'Paseo General Escalón, San Salvador, El Salvador',
        'logo'        => 'assets/img/logo_config.jpg',
    ];
    $stmt = $pdo->prepare('INSERT IGNORE INTO configuracion (clave, valor) VALUES (?, ?)');
    foreach ($cfg as $clave => $valor) {
        $stmt->execute([$clave, $valor]);
    }

    // Una fila por día: sin horas, el panel las rellena en Horarios.
    $stmt = $pdo->prepare('INSERT IGNORE INTO horarios (dia, cerrado) VALUES (?, 0)');
    for ($dia = 1; $dia <= 7; $dia++) {
        $stmt->execute([$dia]);
    }
}