<?php
// index.php (Enrutador Principal)

// Iniciar sesión SIEMPRE al principio
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración básica
define('BASE_URL', '/erp-ium/'); // Ajusta si tu carpeta tiene otro nombre
define('DEFAULT_CONTROLLER', 'user'); // Controlador por defecto si hay sesión
define('DEFAULT_ACTION', 'profile'); // Acción por defecto si hay sesión

// Incluir archivos necesarios
require_once 'db.php'; // Conexión BD
require_once 'password.php'; // Compatibilidad de hash
require_once 'helpers.php'; // Permisos y utilidades

// Determinar controlador y acción
$controllerName = $_GET['controller'] ?? (isset($_SESSION['user_id']) ? DEFAULT_CONTROLLER : 'auth');
$actionName = $_GET['action'] ?? (isset($_SESSION['user_id']) ? DEFAULT_ACTION : 'login');

// Formatear nombres
$controllerClassName = ucfirst($controllerName) . 'Controller';
$controllerFile = 'controllers/' . $controllerClassName . '.php';

// Verificar si el archivo del controlador existe
if (file_exists($controllerFile)) {
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
                // Manejo básico de errores
                error_log("Error ejecutando acción: " . $e->getMessage()); // Registrar error
                die("Ocurrió un error inesperado."); // Mensaje genérico al usuario
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
?>