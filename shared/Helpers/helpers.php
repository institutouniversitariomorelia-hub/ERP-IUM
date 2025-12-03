<?php
// helpers.php - Funciones de ayuda para control de permisos y utilidades genéricas

if (!function_exists('currentUserRole')) {
    function currentUserRole(): ?string {
        return $_SESSION['user_rol'] ?? null;
    }
}

// Mapa de permisos por rol (ajustable). Cada rol lista los módulos que puede ver.
// SU: Súper Usuario, acceso total.
// ADM: Administración.
// COB: Cobranzas.
// REC: Rectoría.
$ROLE_MODULES = [
    'SU'  => ['dashboard','profile','egresos','ingresos','categorias','presupuestos','auditoria','reportes'],
    'ADM' => ['dashboard','profile','egresos','ingresos','categorias','presupuestos','reportes'],
    'COB' => ['dashboard','profile','ingresos','egresos','categorias','presupuestos','reportes'],
    'REC' => ['dashboard','profile','egresos','ingresos','categorias','presupuestos','auditoria','reportes']
];

// Permisos de acciones CRUD por (rol -> módulo -> acciones permitidas)
$ROLE_ACTIONS = [
    'SU'  => ['*' => ['view','add','edit','delete','change_pass']],
    'ADM' => [
        'ingresos' => ['view','add','edit','delete'],
        'egresos' => ['view','add','edit','delete'],
        'categorias' => ['view','add','edit','delete'],
        'presupuestos' => ['view','add','edit','delete'],
        'dashboard' => ['view'],
        'reportes' => ['view'],
        'profile' => ['view']
        // 'user' removido - ADM no gestiona usuarios
    ],
    'COB' => [
        'ingresos' => ['view','add','edit','delete'],
        'egresos' => ['view','add','edit','delete'],
        'categorias' => ['view','add','edit','delete'],
        'presupuestos' => ['view','add','edit','delete'],
        'dashboard' => ['view'],
        'reportes' => ['view'],
        'profile' => ['view']
        // 'user' removido - COB no gestiona usuarios
    ],
    'REC' => [
        'ingresos' => ['view','add','edit','delete'],
        'egresos' => ['view','add','edit','delete'],
        'categorias' => ['view','add','edit','delete'],
        'presupuestos' => ['view','add','edit','delete'],
        'auditoria' => ['view'],
        'dashboard' => ['view'],
        'reportes' => ['view'],
        'profile' => ['view']
        // 'user' removido - Rectoría no gestiona usuarios
    ]
];

/**
 * Verifica si el rol actual puede ver un módulo.
 */
if (!function_exists('roleCanViewModule')) {
    function roleCanViewModule(string $module): bool {
        global $ROLE_MODULES; // usar mapa global
        $rol = currentUserRole();
        if (!$rol) return false;
        if ($rol === 'SU') return true; // acceso total
        return in_array($module, $ROLE_MODULES[$rol] ?? []);
    }
}

/**
 * Verifica si el rol actual puede realizar una acción (add, edit, delete, view) en el módulo.
 */
if (!function_exists('roleCan')) {
    function roleCan(string $action, string $module): bool {
        global $ROLE_ACTIONS;
        $rol = currentUserRole();
        if (!$rol) return false;
        if ($rol === 'SU') return true;
        $map = $ROLE_ACTIONS[$rol] ?? [];
        // Módulo específico
        if (isset($map[$module]) && in_array($action, $map[$module])) return true;
        // comodín '*'
        if (isset($map['*']) && in_array($action, $map['*'])) return true;
        return false;
    }
}

/**
 * Escapar HTML rápido (duplicado de app.js para uso en PHP si se requiere).
 */
function h(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

?>

<?php
// --- Debug / Logging utilities ---
if (!function_exists('is_debug')) {
    function is_debug(): bool {
        return defined('APP_DEBUG') && APP_DEBUG === true;
    }
}

if (!function_exists('debug_log')) {
    function debug_log(string $message, array $context = []): void {
        $ts = date('Y-m-d H:i:s');
        $ctx = $context ? ' | ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
        $line = "[{$ts}] " . $message . $ctx . PHP_EOL;
        $file = defined('DEBUG_LOG_FILE') ? DEBUG_LOG_FILE : __DIR__ . '/../../logs/debug.log';
        // Asegurar directorio
        $dir = dirname($file);
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}

if (!function_exists('debug_var')) {
    function debug_var($var, string $label = null): void {
        $labelPart = $label ? "{$label}: " : '';
        $export = var_export($var, true);
        debug_log($labelPart . $export);
    }
}

// Manejadores globales de errores y excepciones
if (!function_exists('app_exception_handler')) {
    function app_exception_handler($e) {
        $msg = sprintf("Uncaught exception: %s in %s on line %s", $e->getMessage(), $e->getFile(), $e->getLine());
        $context = [
            'uri' => $_SERVER['REQUEST_URI'] ?? null,
            'trace' => $e->getTraceAsString()
        ];
        debug_log($msg, $context);
        if (is_debug()) {
            echo "<pre>" . htmlspecialchars($msg . "\n\n" . $e->getTraceAsString()) . "</pre>";
            return;
        }
        http_response_code(500);
        echo "Ocurrió un error inesperado. Contacte al administrador.";
    }
}

if (!function_exists('app_error_handler')) {
    function app_error_handler($errno, $errstr, $errfile, $errline) {
        // Convertir a excepción para centralizar manejo
        $e = new ErrorException($errstr, 0, $errno, $errfile, $errline);
        app_exception_handler($e);
    }
}

// Registrar manejadores solo una vez
if (!defined('APP_DEBUG_HANDLERS_REGISTERED')) {
    set_exception_handler('app_exception_handler');
    set_error_handler('app_error_handler');
    define('APP_DEBUG_HANDLERS_REGISTERED', true);
}

?>
