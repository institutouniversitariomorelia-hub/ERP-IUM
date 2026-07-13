<?php
// src/Auth/Controllers/AuthController.php

require_once __DIR__ . '/../../../utils/password.php'; 

class AuthController {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Muestra la página/vista del formulario de login.
     */
    public function login() {
        if (isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'index.php?controller=' . DEFAULT_CONTROLLER . '&action=' . DEFAULT_ACTION);
            exit;
        }
        $error = $_GET['error'] ?? null;
        // Verificar si la vista existe
        if (file_exists(__DIR__ . '/../Views/login.php')) {
            require __DIR__ . '/../Views/login.php';
        } else {
            die("Error crítico: No se encontró la vista de login.");
        }
    }

    /**
     * Procesa los datos enviados desde el formulario de login.
     */
    public function processLogin() {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login&error=Usuario y contraseña son requeridos');
            exit;
        }

        $stmt = $this->db->prepare("SELECT id_user, nombre, username, password, rol FROM usuarios WHERE username = ?");
        if (!$stmt) {
             error_log("Error al preparar la consulta de login: " . $this->db->error);
             header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login&error=Error interno del servidor');
             exit;
         }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                session_regenerate_id(true);

// El SELECT debe devolver 'id_user'
$_SESSION['user_id'] = $user['id_user']; 
$_SESSION['user_nombre'] = $user['nombre'];
$_SESSION['user_username'] = $user['username'];
$_SESSION['user_rol'] = $user['rol'];

// <-- ¡IMPORTANTE! Hemos ELIMINADO la línea setAuditUser()
// porque no está definida y no es necesaria.

header('Location: ' . BASE_URL . 'index.php?controller=' . DEFAULT_CONTROLLER . '&action=' . DEFAULT_ACTION);
exit;
            }
        }
        
        $stmt->close(); // Cerrar statement
        header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login&error=Usuario o contraseña incorrectos');
        exit;
    }

    /**
     * Cierra la sesión del usuario.
     */
    public function logout() {
        session_unset(); 
        session_destroy(); 
        header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login');
        exit;
    }
    
    /**
     * Acción AJAX: Cambia la contraseña de un usuario.
     */
    public function changePassword() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $username = $_POST['username'] ?? '';
        $newPassword = $_POST['password'] ?? '';
        $response = ['success' => false];

        if (empty($username) || empty($newPassword)) {
            $response['error'] = 'Usuario y nueva contraseña son requeridos.';
            echo json_encode($response); exit;
        }
        if ($_SESSION['user_rol'] !== 'SU' && $_SESSION['user_username'] !== $username) {
             $response['error'] = 'Permiso denegado.';
             echo json_encode($response); exit;
        }

        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE usuarios SET password = ? WHERE username = ?");
            if ($stmt) {
                $stmt->bind_param("ss", $hashedPassword, $username);
                $success = $stmt->execute();
                if ($success) {
                    // =================================================================
                    // <-- ¡CORRECCIÓN 2: BORRAMOS LA LLAMADA A addAudit()!
                    // El trigger 'trg_usuarios_after_update_aud' se encarga de esto.
                    // =================================================================
                    $response['success'] = true;
                } else {
                     $response['error'] = 'Error al actualizar la contraseña: ' . $stmt->error;
                     error_log("Error al ejecutar changePassword: " . $stmt->error);
                }
                $stmt->close();
            } else {
                $response['error'] = 'Error al preparar la actualización: ' . $this->db->error;
                 error_log("Error al preparar changePassword: " . $this->db->error);
            }
        } catch (Exception $e) {
             error_log("Excepción en AuthController->changePassword: " . $e->getMessage());
             $response['error'] = 'Error interno del servidor al cambiar contraseña.';
        }
        echo json_encode($response);
        exit;
    } // <- Cierre del método changePassword
    
    /**
     * Acción AJAX: Cambia la contraseña del usuario con validación de contraseña actual.
     */
    public function changePasswordWithValidation() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { 
            echo json_encode(['success' => false, 'error' => 'No autorizado']); 
            exit; 
        }

        $username = $_POST['username'] ?? '';
        $passwordActual = $_POST['password_actual'] ?? '';
        $passwordNueva = $_POST['password_nueva'] ?? '';
        $response = ['success' => false];

        if (empty($username) || empty($passwordActual) || empty($passwordNueva)) {
            $response['error'] = 'Todos los campos son requeridos.';
            echo json_encode($response); 
            exit;
        }

        try {
            // Verificar que el usuario exista y obtener su contraseña actual
            $stmt = $this->db->prepare("SELECT password FROM usuarios WHERE username = ?");
            if (!$stmt) {
                $response['error'] = 'Error al preparar la consulta.';
                error_log("Error en changePasswordWithValidation prepare: " . $this->db->error);
                echo json_encode($response); 
                exit;
            }
            
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                // Verificar que la contraseña actual sea correcta
                if (!password_verify($passwordActual, $user['password'])) {
                    $response['error'] = 'La contraseña actual es incorrecta.';
                    echo json_encode($response);
                    $stmt->close();
                    exit;
                }
                
                $stmt->close();
                
                // Actualizar con la nueva contraseña
                $hashedPassword = password_hash($passwordNueva, PASSWORD_DEFAULT);
                $stmtUpdate = $this->db->prepare("UPDATE usuarios SET password = ? WHERE username = ?");
                
                if ($stmtUpdate) {
                    $stmtUpdate->bind_param("ss", $hashedPassword, $username);
                    $success = $stmtUpdate->execute();
                    
                    if ($success) {
                        $response['success'] = true;
                    } else {
                        $response['error'] = 'Error al actualizar la contraseña: ' . $stmtUpdate->error;
                        error_log("Error al ejecutar changePasswordWithValidation update: " . $stmtUpdate->error);
                    }
                    $stmtUpdate->close();
                } else {
                    $response['error'] = 'Error al preparar la actualización: ' . $this->db->error;
                    error_log("Error al preparar changePasswordWithValidation update: " . $this->db->error);
                }
            } else {
                $response['error'] = 'Usuario no encontrado.';
                $stmt->close();
            }
        } catch (Exception $e) {
            error_log("Excepción en AuthController->changePasswordWithValidation: " . $e->getMessage());
            $response['error'] = 'Error interno del servidor al cambiar contraseña.';
        }
        
        echo json_encode($response);
        exit;
    } // <- Cierre del método changePasswordWithValidation
    
} // <- Cierre de la CLASE AuthController
?>