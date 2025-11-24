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
    'ADM' => ['dashboard','profile','egresos','ingresos','categorias','presupuestos','auditoria','reportes'],
    'COB' => ['dashboard','profile','ingresos','egresos','categorias','presupuestos','reportes'],
    'REC' => ['dashboard','profile','ingresos','egresos','categorias','presupuestos','reportes']
];

// Permisos de acciones CRUD por (rol -> módulo -> acciones permitidas)
$ROLE_ACTIONS = [
    'SU'  => ['*' => ['view','add','edit','delete','change_pass']],
    'ADM' => [
        'ingresos' => ['view','add','edit','delete'],
        'egresos' => ['view','add','edit','delete'],
        'categorias' => ['view','add','edit','delete'],
        'presupuestos' => ['view','add','edit','delete'],
        'auditoria' => ['view'],
        'dashboard' => ['view'],
        'reportes' => ['view'],
        'profile' => ['view','change_pass']
        // 'user' removido - ADM no gestiona usuarios
    ],
    'COB' => [
        'ingresos' => ['view','add','edit'],
        'egresos'  => ['view','add','edit'],
        'categorias' => ['view'],
        'presupuestos' => ['view'],
        'reportes' => ['view'],
        'profile' => ['view']
        // 'user' removido - COB no gestiona usuarios
    ],
    'REC' => [
        'ingresos' => ['view','add','edit'],
        'egresos'  => ['view','add','edit'],
        'categorias' => ['view'],
        'presupuestos' => ['view'],
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
