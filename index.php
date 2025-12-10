<?php
// index.php (Enrutador Principal)

// Iniciar sesión SIEMPRE al principio
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// (request debug removed)

// Configuración básica
define('BASE_URL', '/erp-ium/'); // Ajusta si tu carpeta tiene otro nombre
define('DEFAULT_CONTROLLER', 'user'); // Controlador por defecto si hay sesión
define('DEFAULT_ACTION', 'profile'); // Acción por defecto si hay sesión

// Modo debug temporal: activa para mostrar trazas de error en la pantalla
if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', true);
}

// Incluir archivos necesarios
// Cargar configuración de aplicación (APP_DEBUG, rutas de logs)
//require_once __DIR__ . '/config/app.php';

// Conexión a BD
require_once __DIR__ . '/config/database.php'; // Conexión BD
require_once __DIR__ . '/utils/password.php'; // Compatibilidad de hash
require_once __DIR__ . '/shared/Helpers/helpers.php'; // Permisos y utilidades

// Archivos PHP directos que no necesitan enrutador (redirectores, generadores)
// Estos archivos se acceden directamente y NO pasan por index.php
// Esta lista es solo para documentación - el servidor web los sirve directamente
$directFiles = [
    'generate_receipt_ingreso.php',
    'generate_receipt_egreso.php',
    'generate_comparativa_dashboard.php',
    'generate_reporte_consolidado.php',
    'generate_reporte_ingresos.php',
    'generate_reporte_egresos.php'
];

// Determinar controlador y acción (aceptar tanto GET como POST para llamadas AJAX)
$controllerName = $_GET['controller'] ?? $_POST['controller'] ?? (isset($_SESSION['user_id']) ? DEFAULT_CONTROLLER : 'auth');
$actionName = $_GET['action'] ?? $_POST['action'] ?? (isset($_SESSION['user_id']) ? DEFAULT_ACTION : 'login');

// Mapeo de controladores a sus rutas modulares
$controllerMap = [
    'auth' => 'src/Auth/Controllers/AuthController.php',
    'user' => 'src/Auth/Controllers/UserController.php',
    'ingreso' => 'src/Ingresos/Controllers/IngresoController.php',
    'egreso' => 'src/Egresos/Controllers/EgresoController.php',
    'categoria' => 'src/Categorias/Controllers/CategoriaController.php',
    'presupuesto' => 'src/Presupuestos/Controllers/PresupuestoController.php',
    'auditoria' => 'src/Auditoria/Controllers/AuditoriaController.php',
    'dashboard' => 'src/Dashboard/Controllers/DashboardController.php',
    'reporte' => 'src/Reportes/Controllers/ReporteController.php'
];

// Formatear nombres
$controllerClassName = ucfirst($controllerName) . 'Controller';
$controllerFile = $controllerMap[$controllerName] ?? null;

// Verificar si el controlador está mapeado y el archivo existe
// Log básico de la petición para diagnóstico (si está habilitado)
if (function_exists('debug_log')) {
    debug_log('Request routing', [
        'controller' => $controllerName,
        'action' => $actionName,
        'method' => $_SERVER['REQUEST_METHOD'] ?? null,
        'uri' => $_SERVER['REQUEST_URI'] ?? null,
        'session_user' => $_SESSION['user_id'] ?? null
    ]);
}

if ($controllerFile && file_exists(__DIR__ . '/' . $controllerFile)) {
    $controllerFile = __DIR__ . '/' . $controllerFile;
    require_once $controllerFile;
    // Verificar si la clase existe
    if (class_exists($controllerClassName)) {
        // Crear instancia del controlador, pasando la conexión a la BD
        $controller = new $controllerClassName($conn); // $conn viene de db.php
        // Verificar si la acción (método) existe
        if (method_exists($controller, $actionName)) {
            // Llamar a la acción
            try {
                $controller->$actionName();
            } catch (Exception $e) {
                // Registrar en PHP error_log
                error_log("Error ejecutando acción: " . $e->getMessage());
                // Registrar también en nuestro debug.log si la función está disponible
                if (function_exists('debug_log')) {
                    debug_log('Error ejecutando acción', [
                        'controller' => $controllerClassName,
                        'action' => $actionName,
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'line' => $e->getLine()
                    ]);
                }
                // En modo debug mostrar detalles, en producción mostrar mensaje genérico
                if (defined('APP_DEBUG') && APP_DEBUG === true) {
                    echo "<pre>" . htmlspecialchars($e->getMessage() . "\n\n" . $e->getTraceAsString()) . "</pre>";
                } else {
                    die("Ocurrió un error inesperado.");
                }
            }
        } else {
            // Si la acción no existe, mostrar error 404
             error_log("Acción no encontrada: {$controllerClassName}->{$actionName}");
             http_response_code(404);
             die("Error 404: Página no encontrada (acción inválida).");
        }
    } else {
        die("Error: La clase '{$controllerClassName}' no existe en '{$controllerFile}'.");
    }
} else {
     // Si no se encuentra el controlador y no es el login, redirigir a login si no hay sesión
    if ($controllerName !== 'auth' && !isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login');
        exit;
    }
    // Si hay sesión pero el controlador no existe, mostrar error 404
     error_log("Controlador no encontrado: {$controllerFile}");
     http_response_code(404);
     die("Error 404: Página no encontrada (controlador inválido).");
}

// Cerrar conexión (opcional, PHP suele hacerlo)
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}