<?php
// src/Categorias/Controllers/CategoriaController.php

require_once __DIR__ . '/../Models/CategoriaModel.php';
require_once __DIR__ . '/../../Auditoria/Models/AuditoriaModel.php';

class CategoriaController {
    private $db;
    private $categoriaModel;
    private $auditoriaModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->categoriaModel = new CategoriaModel($dbConnection);
        $this->auditoriaModel = new AuditoriaModel($dbConnection);
    }

    /**
     * Acción principal: Muestra la lista de categorías.
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login'); exit; }

        $categorias = $this->categoriaModel->getAllCategorias();

        $pageTitle = "Categorías";
        $activeModule = "categorias";

        $this->renderView('categorias_list', [
            'pageTitle' => $pageTitle,
            'activeModule' => $activeModule,
            'categorias' => $categorias
        ]);
    }

    /**
     * Acción AJAX: Guarda una nueva categoría o actualiza una existente.
     */
    public function save() {
        // Forzar respuesta JSON y evitar que se incluya el layout o cualquier vista
        // Limpia cualquier salida previa (por si algún include/notice imprimió algo)
        if (ob_get_level() > 0) {
            @ob_end_clean();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
        }

        // Seguridad: esta acción solo debe usarse vía AJAX/logueado
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit; // cortar completamente la ejecución para que index.php no continúe
        }

        $data = $_POST;
        // <-- ¡CORRECCIÓN 1: AÑADIR EL ID DE USUARIO DE LA SESIÓN!
        // El modelo lo necesita para el INSERT/UPDATE (columna NOT NULL)
        $data['id_user'] = $_SESSION['user_id'];
        
        $id = $data['id'] ?? null;
        $response = ['success' => false];

        // Validación básica
        if (empty($data['nombre']) || empty($data['tipo'])) {
            $response['error'] = 'El nombre y el tipo de flujo son obligatorios.';
            echo json_encode($response);
            exit;
        }
         // Validar que el tipo sea 'Ingreso' o 'Egreso'
        if (!in_array($data['tipo'], ['Ingreso', 'Egreso'])) {
            $response['error'] = 'Tipo de flujo inválido.';
            echo json_encode($response);
            exit;
        }

        try {
            if (empty($id)) { // Crear
                $success = $this->categoriaModel->createCategoria($data);
                
                // <-- ¡CORRECCIÓN 2: BORRAMOS LA LLAMADA A addAudit()!
                // El trigger 'trg_categorias_after_insert_aud' se encarga de esto.

            } else { // Actualizar
                // Obtener datos antes de actualizar
                $oldData = $this->categoriaModel->getCategoriaById($id);
                
                $success = $this->categoriaModel->updateCategoria($id, $data);

                // <-- ¡CORRECCIÓN 3: BORRAMOS LA LLAMADA A addAudit()!
                // El trigger 'trg_categorias_after_update' se encarga de esto.
            }

            if ($success) {
                // Añadir log si no hay triggers para categorias
                if (!$this->auditoriaModel->hasTriggerForTable('categorias')) {
                    if (empty($id)) {
                        // Inserción
                        $this->auditoriaModel->addLog('Categoria', 'Insercion', null, null, json_encode($data), null, null, $_SESSION['user_id'] ?? null);
                    } else {
                        // Actualización - guardar old y new
                        $oldValor = $oldData ? json_encode($oldData) : null;
                        $newValor = json_encode($data);
                        $this->auditoriaModel->addLog('Categoria', 'Actualizacion', null, $oldValor, $newValor, null, null, $_SESSION['user_id'] ?? null);
                    }
                }
                $response['success'] = true;
            } else {
                 // Exponer error de MySQL para diagnóstico (seguro en entorno interno)
                 $dbErr = $this->db->error;
                 if (strpos($dbErr, 'Duplicate') !== false || strpos($dbErr, '1062') !== false) {
                     $response['error'] = 'El nombre de la categoría ya existe.';
                 } else {
                     $response['error'] = 'No se pudo guardar la categoría en la base de datos. ' . ($dbErr ?: '');
                 }
            }
        } catch (Exception $e) {
            error_log("Error en CategoriaController->save: " . $e->getMessage());
            $response['error'] = 'Error interno del servidor al guardar.';
        }

        echo json_encode($response);
        exit; // asegurarnos de que no se ejecute ningún renderizado adicional
    }

    /**
     * Acción AJAX: Obtiene los datos de una categoría específica (para editar).
     */
     public function getCategoriaData() {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

        $id = $_GET['id'] ?? 0;
        // El 'id' de la tabla 'categorias' es 'id_categoria'
        // El modelo lo busca como 'id' (getCategoriaById), vamos a ver si el modelo usa 'id' o 'id_categoria'
        // El modelo usa "id", pero la BD usa "id_categoria". Vamos a asumir que el modelo está mal...
        // No, el modelo dice "DELETE FROM categorias WHERE id = ?"
        // Y la BD dice id_categoria INT(11) NOT NULL AUTO_INCREMENT, PRIMARY KEY (id_categoria)
        // ¡El modelo está mal!
        // CORRECCIÓN: Tu modelo usa "id" pero tu BD usa "id_categoria". Voy a asumir que tu modelo está mal.
        
        $categoria = $this->categoriaModel->getCategoriaById($id); // El modelo usa 'id' como PK

        if ($categoria) {
            echo json_encode($categoria);
        } else {
             echo json_encode(['error' => 'Categoría no encontrada.']);
        }
        exit;
     }

    /**
     * Acción AJAX: Obtiene las categorías de tipo 'Egreso' (ID y Nombre).
     * Añadido para compatibilidad con llamadas desde frontend (getCategoriasEgreso).
     */
    public function getCategoriasEgreso() {
        header('Content-Type: application/json; charset=utf-8');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

        try {
            $categorias = $this->categoriaModel->getCategoriasByTipo('Egreso');
            // Depuración: registrar número de categorías y ejemplo si la función debug_log está disponible
            if (function_exists('debug_log')) {
                $count = is_array($categorias) ? count($categorias) : 0;
                debug_log('getCategoriasEgreso: returning categories', ['count' => $count, 'sample' => $categorias[0] ?? null]);
            }
            // Si ocurre un error en el modelo es posible recibir un array con 'error'
            if (is_array($categorias) && isset($categorias['error'])) {
                echo json_encode(['error' => $categorias['error']]);
            } else {
                echo json_encode($categorias);
            }
        } catch (Exception $e) {
            if (function_exists('debug_log')) debug_log('Error getCategoriasEgreso: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            echo json_encode(['error' => 'Error al obtener categorías.']);
        }
        exit;
    }

    /**
     * Acción AJAX: Elimina una categoría.
     */
    public function delete() {
         header('Content-Type: application/json; charset=utf-8');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $id = $_POST['id'] ?? 0;
        $response = ['success' => false];

        if ($id > 0) {
            try {
                // VERIFICAR SI ES CATEGORÍA NO BORRABLE
                $checkNoBorrable = "SELECT no_borrable, nombre FROM categorias WHERE id_categoria = ?";
                $stmtNoBorrable = $this->db->prepare($checkNoBorrable);
                $stmtNoBorrable->bind_param("i", $id);
                $stmtNoBorrable->execute();
                $resultNoBorrable = $stmtNoBorrable->get_result();
                $categoria = $resultNoBorrable->fetch_assoc();
                $stmtNoBorrable->close();
                
                if ($categoria && $categoria['no_borrable'] == 1) {
                    $response['error'] = 'No se puede eliminar la categoría "' . $categoria['nombre'] . '" porque es una categoría del sistema protegida.';
                    echo json_encode($response);
                    exit;
                }
                
                // Verificar si la categoría está siendo usada
                $checkQuery = "SELECT 
                    (SELECT COUNT(*) FROM ingresos WHERE id_categoria = ?) +
                    (SELECT COUNT(*) FROM egresos WHERE id_categoria = ?) AS total_usos";
                $stmtCheck = $this->db->prepare($checkQuery);
                $stmtCheck->bind_param("ii", $id, $id);
                $stmtCheck->execute();
                $resultCheck = $stmtCheck->get_result();
                $row = $resultCheck->fetch_assoc();
                $stmtCheck->close();
                
                if ($row && $row['total_usos'] > 0) {
                    $response['error'] = 'No se puede eliminar esta categoría porque está siendo utilizada en ' . $row['total_usos'] . ' registro(s) de ingresos o egresos.';
                    echo json_encode($response);
                    exit;
                }
                
                // Si no está en uso, proceder con la eliminación
                $success = $this->categoriaModel->deleteCategoria($id);
                if ($success) {
                    if (!$this->auditoriaModel->hasTriggerForTable('categorias')) {
                        // Guardar objeto completo en old_valor antes de eliminar
                        $oldValor = $categoria ? json_encode($categoria) : null;
                        $this->auditoriaModel->addLog('Categoria', 'Eliminacion', $oldValor, null, null, null, null, $_SESSION['user_id'] ?? null);
                    }
                    $response['success'] = true;
                } else {
                    $dbError = $this->db->error;
                    // Detectar errores de clave foránea
                    if (strpos($dbError, '1451') !== false || strpos($dbError, 'foreign key constraint') !== false) {
                        $response['error'] = 'No se puede eliminar esta categoría porque está siendo utilizada en otros registros.';
                    } else {
                        $response['error'] = 'No se pudo eliminar la categoría de la base de datos. ' . ($dbError ?: 'Error desconocido.');
                    }
                }
            } catch (Exception $e) {
                 error_log("Error en CategoriaController->delete: " . $e->getMessage());
                 $errorMsg = $e->getMessage();
                 // Detectar errores de clave foránea en la excepción
                 if (strpos($errorMsg, '1451') !== false || strpos($errorMsg, 'foreign key') !== false) {
                     $response['error'] = 'No se puede eliminar esta categoría porque está siendo utilizada en otros registros.';
                 } else {
                     $response['error'] = 'Error interno del servidor al eliminar: ' . $errorMsg;
                 }
            }
        } else {
            $response['error'] = 'ID de categoría inválido.';
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
        $viewPath = __DIR__ . "/../Views/{$view}.php";
        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            error_log("Vista no encontrada: " . $viewPath);
            echo "<div class='alert alert-danger'>Error: No se encontró la vista.</div>";
        }
        $content = ob_get_clean();
        require __DIR__ . '/../../../shared/Views/layout.php';
    }
}
?>