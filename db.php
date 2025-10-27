<?php
// db.php (Conexión a BD y Funciones Auxiliares)

// Iniciar sesión si no está iniciada (puede ser redundante con index.php, pero asegura)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración de la conexión
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Contraseña vacía por defecto en XAMPP
define('DB_NAME', 'erp_ium'); // Nombre de tu base de datos

// Crear la conexión
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Verificar la conexión
if ($conn->connect_error) {
    // Es mejor morir con un error claro si la BD no conecta
    error_log("Error de conexión a la base de datos: " . $conn->connect_error); // Registrar error real
    die("Error de conexión con la base de datos. Por favor, intente más tarde."); // Mensaje genérico
}

// Establecer el charset para evitar problemas con acentos y caracteres especiales
if (!$conn->set_charset("utf8mb4")) {
     error_log("Error al establecer utf8mb4: " . $conn->error);
}

// Función para registrar en la auditoría
function addAudit($conn, $seccion, $accion, $detalles) {
    // Asegurarse de que $conn es un objeto mysqli válido
    if (!$conn || !($conn instanceof mysqli)) {
        error_log("Error de auditoría: Conexión a BD no válida.");
        return;
    }
    
    $usuario = $_SESSION['user_username'] ?? 'Sistema'; // Usar el username de la sesión si está disponible
    
    $stmt = $conn->prepare("INSERT INTO auditoria (usuario, seccion, accion, detalles) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("ssss", $usuario, $seccion, $accion, $detalles);
        if (!$stmt->execute()) {
             error_log("Error al ejecutar statement de auditoría: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("Error al preparar statement de auditoría: " . $conn->error);
    }
}
?>