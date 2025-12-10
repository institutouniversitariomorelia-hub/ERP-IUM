<?php
// db.php (Conexión a BD y Funciones Auxiliares - MODIFICADO PARA TRIGGERS)

// Validar y limpiar PHPSESSID inválido antes de iniciar sesión
if (isset($_COOKIE[session_name()])) {
    $sid = $_COOKIE[session_name()];
    if (!preg_match('/^[A-Za-z0-9,-]+$/', $sid)) {
        // Borrar cookie inválida para evitar warnings en session_start()
        setcookie(session_name(), '', time() - 3600, '/');
        unset($_COOKIE[session_name()]);
    }
}

// Configurar parámetros seguros para la cookie de sesión
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
$cookieParams = [
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax'
];
if (PHP_VERSION_ID >= 70300) {
    session_set_cookie_params($cookieParams);
} else {
    session_set_cookie_params($cookieParams['lifetime'], $cookieParams['path'] . '; samesite=' . $cookieParams['samesite'], $cookieParams['domain'], $cookieParams['secure'], $cookieParams['httponly']);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'erp_ium');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    error_log("Error de conexión a la base de datos: " . $conn->connect_error);
    die("Error de conexión con la base de datos. Por favor, intente más tarde.");
}

if (!$conn->set_charset("utf8mb4")) {
     error_log("Error al establecer utf8mb4: " . $conn->error);
}

// Las funciones de permisos se han centralizado en helpers.php.
// Se eliminaron aquí para evitar 'Cannot redeclare ...'. Asegúrate de incluir helpers.php después de este archivo (index.php ya lo hace).

// **CAMBIO IMPORTANTE PARA AUDITORÍA**
// Establecer la variable de sesión de MySQL para los Triggers de Auditoría.
// Tus triggers (ej: trg_ingresos_after_insert_aud) usan @auditoria_user_id.
// El procedimiento (sp_auditar_accion) espera p_user_id, que es alimentado por @auditoria_user_id o NEW.id_user.
if (isset($_SESSION['user_id'])) {
    $userId = (int)$_SESSION['user_id'];
    // Establecemos la variable de sesión de MySQL que usan tus triggers
    $conn->query("SET @auditoria_user_id = $userId");
} else {
    // Si no hay sesión (ej: script de consola), poner un ID por defecto (ej: 0 para 'Sistema')
     $conn->query("SET @auditoria_user_id = 0"); 
}

// --- La función addAudit() SE ELIMINA ---
// Ya no es necesaria, la base de datos (Triggers) se encarga de esto automáticamente.
?>