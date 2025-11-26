<?php
// src/Auth/Controllers/UserController.php

require_once __DIR__ . '/../Models/UserModel.php';
require_once __DIR__ . '/../../Auditoria/Models/AuditoriaModel.php';

class UserController {
    private $db;
    private $userModel;
    private $auditoriaModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->userModel = new UserModel($dbConnection);
        $this->auditoriaModel = new AuditoriaModel($dbConnection);
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

        $data = $_POST;
        $id = isset($data['id']) && $data['id'] !== '' ? intval($data['id']) : null;
        $isUpdate = !empty($id);
        $response = ['success' => false];
        
        // Verificar si el usuario está editando su propio perfil o es SU
        $isOwnProfile = ($id === intval($_SESSION['user_id']));
        $isSuperUser = ($_SESSION['user_rol'] === 'SU');
        
        // Log de depuración
        error_log("UserController->save: id=$id, session_user_id=" . $_SESSION['user_id'] . ", isOwnProfile=" . ($isOwnProfile ? 'true' : 'false'));
        
        // Solo SU puede crear usuarios o editar otros perfiles
        if (!$isOwnProfile && !$isSuperUser) {
            echo json_encode(['success' => false, 'error' => 'Permiso denegado.']); 
            exit;
        }

        if (empty($data['nombre']) || empty($data['username']) || empty($data['rol'])) {
            $response['error'] = 'Nombre, Usuario y Rol son obligatorios.';
            echo json_encode($response); exit;
        }
        if (!$isUpdate && empty($data['password'])) {
            $response['error'] = 'La contraseña es obligatoria para nuevos usuarios.';
            echo json_encode($response); exit;
        }

        try {
            $oldData = $isUpdate ? $this->userModel->getUserById($id) : null;
            if ($isUpdate) { 
                if (!empty($data['password'])) {
                    $hash = password_hash($data['password'], PASSWORD_DEFAULT);
                    $stmt = $this->db->prepare("UPDATE usuarios SET nombre=?, username=?, password=?, rol=? WHERE id_user=?");
                    if(!$stmt) throw new Exception("Error al preparar update (con pass): ".$this->db->error);
                    $stmt->bind_param("ssssi", $data['nombre'], $data['username'], $hash, $data['rol'], $id);
                } else {
                    $stmt = $this->db->prepare("UPDATE usuarios SET nombre=?, username=?, rol=? WHERE id_user=?");
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
                // SOLO actualizar la sesión si el usuario editó su propio perfil
                if ($isOwnProfile && $isUpdate) {
                    error_log("UserController->save: Actualizando sesión del usuario propio");
                    $_SESSION['user_nombre'] = $data['nombre'];
                    $_SESSION['user_username'] = $data['username'];
                    $_SESSION['user_rol'] = $data['rol'];
                    // NO actualizar la contraseña en sesión, eso no tiene sentido
                }
                
                // Fallback en PHP si no hay triggers para usuarios
                if (!$this->auditoriaModel->hasTriggerForTable('usuarios')) {
                    $newData = $this->userModel->getUserById($this->db->insert_id ?: $id);
                    $this->auditoriaModel->addLog('Usuario', $isUpdate ? 'Actualizacion' : 'Insercion', 'Usuario guardado: ' . ($newData['username'] ?? ''), json_encode($oldData), json_encode($newData), null, null, $_SESSION['user_id'] ?? null);
                }
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
            // $userToDelete = $this->userModel->getUserById($id); // No es necesario, el trigger lo registra
            
            // <-- ¡CORRECCIÓN DE BD!
            $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id_user = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                $success = $stmt->execute();
                if ($success) {
                    // Registrar old data si es posible
                    $userToDelete = $this->userModel->getUserById($id);
                    if (!$this->auditoriaModel->hasTriggerForTable('usuarios')) {
                        $oldValor = $userToDelete ? json_encode($userToDelete) : null;
                        $this->auditoriaModel->addLog('Usuario', 'Eliminacion', null, $oldValor, null, null, null, $_SESSION['user_id'] ?? null);
                    }
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
        // Extraer datos primero para que estén disponibles en la vista
        extract($data);
        
        // Capturar el output de la vista parcial
        ob_start();
        $viewPath = __DIR__ . "/../Views/{$view}.php";
        if (file_exists($viewPath)) {
             require $viewPath;
        } else {
             error_log("Vista no encontrada: " . $viewPath);
             echo "<div class='alert alert-danger'>Error: No se encontró la plantilla de la vista.</div>";
        }
        $content = ob_get_clean(); 
        
        // Cargar el layout - necesitamos subir 3 niveles: Controllers -> Auth -> src -> raíz
        $layoutPath = realpath(__DIR__ . '/../../../shared/Views/layout.php');
        if ($layoutPath && file_exists($layoutPath)) {
             require $layoutPath; 
        } else {
             die("Error crítico: No se encontró el archivo layout.php. Path buscado: " . __DIR__ . '/../../../shared/Views/layout.php');
        }
    } // <- Cierre del método renderView
    
} 
?>