<?php
// config/app.php - Configuración de la aplicación (modo debug, logs)

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Activar modo debug mediante variable de entorno APP_DEBUG o por defecto false.
$envDebug = getenv('APP_DEBUG');
if ($envDebug === false) {
    $envDebug = $_ENV['APP_DEBUG'] ?? null;
}
$debug = false;
if ($envDebug !== null) {
    $debug = in_array(strtolower((string)$envDebug), ['1','true','on','yes'], true);
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', $debug);
}

// Ruta del directorio de logs y archivo por defecto
if (!defined('LOG_DIR')) {
    define('LOG_DIR', __DIR__ . '/../logs');
}
if (!defined('DEBUG_LOG_FILE')) {
    define('DEBUG_LOG_FILE', LOG_DIR . '/debug.log');
}

// Asegurar que el directorio de logs exista
if (!is_dir(LOG_DIR)) {
    @mkdir(LOG_DIR, 0775, true);
}

// Si APP_DEBUG está activado, activar reporting de errores en PHP
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', '0');
}

?>