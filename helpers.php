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
    'SU'  => ['profile','egresos','ingresos','categorias','presupuestos','auditoria'],
    'ADM' => ['profile','egresos','ingresos','categorias','presupuestos','auditoria'],
    'COB' => ['profile','ingresos','egresos','categorias','presupuestos'],
    'REC' => ['profile','ingresos','egresos','categorias','presupuestos']
];

// Permisos de acciones CRUD por (rol -> módulo -> acciones permitidas)
$ROLE_ACTIONS = [
    'SU'  => ['*' => ['view','add','edit','delete']],
    'ADM' => ['*' => ['view','add','edit','delete']],
    'COB' => [
        'ingresos' => ['view','add','edit'],
        'egresos'  => ['view','add','edit'],
        'categorias' => ['view'],
        'presupuestos' => ['view'],
        'profile' => ['view']
    ],
    'REC' => [
        'ingresos' => ['view','add','edit'],
        'egresos'  => ['view','add','edit'],
        'categorias' => ['view'],
        'presupuestos' => ['view'],
        'profile' => ['view']
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
