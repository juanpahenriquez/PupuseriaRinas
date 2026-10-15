<?php
/** Sesiones, login y CSRF del panel administrativo. */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function csrf_input(): string
{
    return '<input type="hidden" name="csrf" value="' . csrf_token() . '">';
}

function verificar_csrf(): void
{
    $t = (string) ($_POST['csrf'] ?? '');
    if ($t === '' || !hash_equals($_SESSION['csrf'] ?? '', $t)) {
        http_response_code(400);
        exit('Token de seguridad inválido. Recarga la página e intenta de nuevo.');
    }
}

function usuario_actual(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requerir_login(): void
{
    if (!usuario_actual()) {
        header('Location: login.php');
        exit;
    }
}

function login(string $email, string $password): bool
{
    $stmt = db()->prepare("SELECT * FROM usuarios WHERE email = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if (!$u || !password_verify($password, $u['password_hash'])) {
        return false;
    }
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'      => (int) $u['id'],
        'nombre'  => $u['nombre'],
        'email'   => $u['email'],
        'rol'     => $u['rol'],
    ];
    return true;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Mensaje flash: guarda */
function flash(string $tipo, string $msg): void
{
    $_SESSION['flash_' . $tipo] = $msg;
}

/** Mensaje flash: lee y limpia */
function leer_flash(string $tipo): ?string
{
    $m = $_SESSION['flash_' . $tipo] ?? null;
    unset($_SESSION['flash_' . $tipo]);
    return $m;
}
