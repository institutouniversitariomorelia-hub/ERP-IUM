<?php
// controllers/UserController.php

require_once 'models/UserModel.php'; // Incluir el modelo

class UserController {
    private $db;
    private $userModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->userModel = new UserModel($dbConnection);
    }

    /**
     * Acción principal: Muestra la página de "Mi Perfil".
     */
    public function profile() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login');
            exit;
        }

        $users = $this->userModel->getAllUsers();
        
        $currentUser = [
            'id' => $_SESSION['user_id'],
            'nombre' => $_SESSION['user_nombre'],
            'username' => $_SESSION['user_username'],
            'rol' => $_SESSION['user_rol']
        ];
        
        $pageTitle = "Mi Perfil";
        $activeModule = "profile";

        $this->renderView('profile', [
            'pageTitle' => $pageTitle,
            'activeModule' => $activeModule,
            'users' => $users,
            'currentUser' => $currentUser 
        ]);
    }

    /**
     * Acción AJAX: Guarda un nuevo usuario o actualiza uno existente.
     */
    public function save() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }
        if ($_SESSION['user_rol'] !== 'SU') { echo json_encode(['success' => false, 'error' => 'Permiso denegado.']); exit; }

        $data = $_POST;
        $id = $data['id'] ?? null;
        $isUpdate = !empty($id);
        $response = ['success' => false];

        if (empty($data['nombre']) || empty($data['username']) || empty($data['rol'])) {
            $response['error'] = 'Nombre, Usuario y Rol son obligatorios.';
            echo json_encode($response); exit;
        }
        if (!$isUpdate && empty($data['password'])) {
            $response['error'] = 'La contraseña es obligatoria para nuevos usuarios.';
            echo json_encode($response); exit;
        }

        try {
            if ($isUpdate) { 
                if (!empty($data['password'])) {
                    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                    $stmt = $this->db->prepare("UPDATE usuarios SET nombre=?, username=?, password=?, rol=? WHERE id=?");
                    if(!$stmt) throw new Exception("Error al preparar update (con pass): ".$this->db->error);
                    $stmt->bind_param("ssssi", $data['nombre'], $data['username'], $hash, $data['rol'], $id);
                } else {
                    $stmt = $this->db->prepare("UPDATE usuarios SET nombre=?, username=?, rol=? WHERE id=?");
                     if(!$stmt) throw new Exception("Error al preparar update (sin pass): ".$this->db->error);
                    $stmt->bind_param("sssi", $data['nombre'], $data['username'], $data['rol'], $id);
                }
            } else { 
                $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, username, password, rol) VALUES (?, ?, ?, ?)");
                 if(!$stmt) throw new Exception("Error al preparar insert: ".$this->db->error);
                $stmt->bind_param("ssss", $data['nombre'], $data['username'], $hash, $data['rol']);
            }

            if ($stmt->execute()) {
                $action = $isUpdate ? 'Actualización' : 'Creación';
                addAudit($this->db, 'Usuario', $action, "Usuario {$data['username']}");
                $response['success'] = true;
            } else {
                $errorMsg = $stmt->error;
                if ($this->db->errno === 1062) {
                     $response['error'] = 'El nombre de usuario ya existe.';
                } else {
                    $response['error'] = 'No se pudo guardar el usuario: ' . $errorMsg;
                }
                error_log("Error al guardar usuario: " . $errorMsg);
            }
            $stmt->close();

        } catch (Exception $e) {
            error_log("Excepción en UserController->save: " . $e->getMessage());
            $response['error'] = 'Error interno del servidor al guardar.';
        }
        echo json_encode($response);
        exit;
    }

    /**
     * Acción AJAX: Elimina un usuario.
     */
    public function delete() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }
         if ($_SESSION['user_rol'] !== 'SU') { echo json_encode(['success' => false, 'error' => 'Permiso denegado.']); exit; }

        $id = $_POST['id'] ?? 0;
        $response = ['success' => false];

        if ($id <= 0) { $response['error'] = 'ID de usuario inválido.'; echo json_encode($response); exit; }
        if ($id == $_SESSION['user_id']) { $response['error'] = 'No puedes eliminar tu propio usuario.'; echo json_encode($response); exit; }

        try {
            $userToDelete = $this->userModel->getUserById($id);
            $usernameToDelete = $userToDelete ? $userToDelete['username'] : "ID {$id}";

            $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                $success = $stmt->execute();
                if ($success) {
                    addAudit($this->db, 'Usuario', 'Eliminación', "Usuario {$usernameToDelete}");
                    $response['success'] = true;
                } else {
                    $response['error'] = 'No se pudo eliminar el usuario: ' . $stmt->error;
                     error_log("Error al eliminar usuario: " . $stmt->error);
                }
                $stmt->close();
            } else {
                $response['error'] = 'Error al preparar la eliminación: ' . $this->db->error;
                error_log("Error al preparar delete usuario: " . $this->db->error);
            }
        } catch (Exception $e) {
             error_log("Excepción en UserController->delete: " . $e->getMessage());
             $response['error'] = 'Error interno del servidor al eliminar.';
        }
        echo json_encode($response);
        exit;
    }
    
     /**
     * Función helper para renderizar vistas.
     */
    protected function renderView($view, $data = []) {
        extract($data);
        ob_start();
        // Verificar si el archivo de vista existe antes de incluirlo
        if (file_exists("views/{$view}.php")) {
            require "views/{$view}.php";
        } else {
            error_log("Vista no encontrada: views/{$view}.php");
            echo "<div class='alert alert-danger'>Error: No se encontró la plantilla de la vista.</div>"; // Mostrar error en la vista
        }
        $content = ob_get_clean(); 
        // Verificar si el layout existe antes de incluirlo
        if (file_exists('views/layout.php')) {
             require 'views/layout.php'; 
        } else {
             die("Error crítico: No se encontró el archivo layout.php"); // Error fatal si no hay layout
        }
    } // <- Cierre del método renderView
    
} 
?> 