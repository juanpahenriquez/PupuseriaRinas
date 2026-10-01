<?php
/**
 * Conexión PDO a MySQL — WAMP (root / sin contraseña).
 * Crea la BD, las tablas y datos iniciales la primera vez que se usa.
 */
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'pupuseria_rinas');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

require_once __DIR__ . '/util.php';

/**
 * Conexión PDO a MySQL (WAMP: root / sin contraseña).
 * @param bool $fatale Si true y MySQL no responde, muestra un error y detiene
 *                     (uso admin). Si false, devuelve null (uso público: la
 *                     página sigue funcionando con los datos por defecto).
 */
function db(bool $fatale = true): ?PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $charset = 'charset=' . DB_CHARSET;

    $ultimoError = null;
    foreach ([DB_HOST, 'localhost'] as $host) {
        try {
            $dsn = "mysql:host=$host;$charset";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
            $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
            $pdo->exec('USE `' . DB_NAME . '`');
            // Instala el esquema solo si aún no existe (evita re-importar el JSON de productos)
            $yaInstalado = $pdo->query("SHOW TABLES LIKE 'usuarios'")->fetch();
            if (!$yaInstalado) {
                instalar_esquema($pdo);
            }
            return $pdo;
        } catch (PDOException $e) {
            $ultimoError = $e;
            $pdo = null;
        }
    }

    if (!$fatale) {
        return null;
    }
    http_response_code(500);
    exit('No se pudo conectar a MySQL. ¿Está WAMP encendido (servicio MySQL)?<br>'
        . '<small>' . htmlspecialchars($ultimoError->getMessage() ?? '') . '</small>');
}

/** Crea tablas si no existen e importa datos iniciales. */
function instalar_esquema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        rol ENUM('admin','cajero') NOT NULL DEFAULT 'cajero',
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS productos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(120) NOT NULL,
        descripcion VARCHAR(255) NOT NULL DEFAULT 'Sin descripción',
        precio DECIMAL(10,2) NOT NULL DEFAULT 0,
        categoria ENUM('Pupusas','Bebidas','Complementos') NOT NULL DEFAULT 'Pupusas',
        estado ENUM('Activo','Agotado') NOT NULL DEFAULT 'Activo',
        imagen VARCHAR(255) DEFAULT NULL,
        creado DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS pedidos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        folio VARCHAR(20) NOT NULL DEFAULT '',
        tipo ENUM('llevar','recoger') NOT NULL DEFAULT 'recoger',
        cliente VARCHAR(120) NOT NULL DEFAULT '',
        telefono VARCHAR(30) NOT NULL DEFAULT '',
        notas VARCHAR(255) NOT NULL DEFAULT '',
        total DECIMAL(10,2) NOT NULL DEFAULT 0,
        estado ENUM('pendiente','cocina','listo','entregado') NOT NULL DEFAULT 'pendiente',
        creado DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (estado), INDEX (creado)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS pedido_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        pedido_id INT NOT NULL,
        producto_id INT DEFAULT NULL,
        nombre VARCHAR(120) NOT NULL,
        cantidad INT NOT NULL DEFAULT 1,
        precio DECIMAL(10,2) NOT NULL DEFAULT 0,
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS horarios (
        dia TINYINT UNSIGNED NOT NULL PRIMARY KEY COMMENT '1=Lunes .. 7=Domingo',
        am_apertura TIME NULL,
        am_cierre TIME NULL,
        pm_apertura TIME NULL,
        pm_cierre TIME NULL,
        cerrado TINYINT(1) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS configuracion (
        clave VARCHAR(60) NOT NULL PRIMARY KEY,
        valor TEXT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Admin inicial (cambiar la contraseña después de entrar)
    $hayAdmin = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='admin'")->fetchColumn();
    if ($hayAdmin === 0) {
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol, activo) VALUES (?,?,?,'admin',1)");
        $stmt->execute(['Admin Rinas', 'admin@rinas.com', password_hash('admin123', PASSWORD_DEFAULT)]);
    }

    // 7 filas de horario vacías (para que el formulario tenga filas)
    if ((int) $pdo->query("SELECT COUNT(*) FROM horarios")->fetchColumn() === 0) {
        $pdo->exec("INSERT IGNORE INTO horarios (dia) VALUES (1),(2),(3),(4),(5),(6),(7)");
    }

    // Migración: importa data/productos.json solo la primera vez (tabla recién creada)
    $jsonFile = dirname(__DIR__) . '/data/productos.json';
    if (is_file($jsonFile)) {
        $rows = json_decode((string) file_get_contents($jsonFile), true);
        if (is_array($rows) && $rows) {
            try {
                $ins = $pdo->prepare("INSERT INTO productos (id, nombre, descripcion, precio, categoria, estado, imagen, creado) VALUES (?,?,?,?,?,?,?,?)");
                foreach ($rows as $p) {
                    if (empty($p['id']) || empty($p['nombre'])) {
                        continue;
                    }
                    $ins->execute([
                        (int) $p['id'],
                        $p['nombre'],
                        $p['descripcion'] ?? 'Sin descripción',
                        $p['precio'] ?? 0,
                        in_array($p['categoria'] ?? '', ['Pupusas', 'Bebidas', 'Complementos'], true) ? $p['categoria'] : 'Pupusas',
                        $p['estado'] ?? 'Activo',
                        $p['imagen'] ?? null,
                        $p['creado'] ?? date('Y-m-d H:i:s'),
                    ]);
                }
                $maxId = (int) max(array_column($rows, 'id'));
                if ($maxId > 0) {
                    $pdo->exec('ALTER TABLE productos AUTO_INCREMENT = ' . ($maxId + 1));
                }
            } catch (PDOException $e) {
                // la importación es best-effort: no bloquea el panel
            }
        }
    }
}
