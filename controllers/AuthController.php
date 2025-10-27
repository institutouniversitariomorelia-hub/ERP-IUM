<?php
// controllers/AuthController.php

// Asegúrate de que password.php esté en la raíz del proyecto
require_once 'password.php'; 

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
        if (file_exists('views/login.php')) {
            require 'views/login.php';
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

        $stmt = $this->db->prepare("SELECT id, nombre, username, password, rol FROM usuarios WHERE username = ?");
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
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nombre'] = $user['nombre'];
                $_SESSION['user_username'] = $user['username'];
                $_SESSION['user_rol'] = $user['rol'];
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
                    addAudit($this->db, 'Usuario', 'Actualización', "Contraseña cambiada para {$username}");
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
    
} // <- Cierre de la CLASE AuthController
?> // <- Cierre opcional de PHP


