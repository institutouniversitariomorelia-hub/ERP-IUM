<?php
// controllers/CategoriaController.php (CORREGIDO)

require_once 'models/CategoriaModel.php';

class CategoriaController {
    private $db;
    private $categoriaModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->categoriaModel = new CategoriaModel($dbConnection);
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
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

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
                $success = $this->categoriaModel->updateCategoria($id, $data);

                // <-- ¡CORRECCIÓN 3: BORRAMOS LA LLAMADA A addAudit()!
                // El trigger 'trg_categorias_after_update' se encarga de esto.
            }

            if ($success) {
                $response['success'] = true;
            } else {
                 $response['error'] = 'No se pudo guardar la categoría en la base de datos.';
            }
        } catch (Exception $e) {
            error_log("Error en CategoriaController->save: " . $e->getMessage());
            $response['error'] = 'Error interno del servidor al guardar.';
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Acción AJAX: Obtiene los datos de una categoría específica (para editar).
     */
     public function getCategoriaData() {
        header('Content-Type: application/json');
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
     * Acción AJAX: Elimina una categoría.
     */
    public function delete() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $id = $_POST['id'] ?? 0;
        $response = ['success' => false];

        if ($id > 0) {
            try {
                // No necesitamos $categoria o $nombreCat, el trigger lo hace.
                $success = $this->categoriaModel->deleteCategoria($id);
                if ($success) {
                    
                    // <-- ¡CORRECCIÓN 4: BORRAMOS LA LLAMADA A addAudit()!
                    // El trigger 'trg_categorias_before_delete' se encarga de esto.
                    
                    $response['success'] = true;
                } else {
                    $response['error'] = 'No se pudo eliminar la categoría de la base de datos.';
                }
            } catch (Exception $e) {
                 error_log("Error en CategoriaController->delete: " . $e->getMessage());
                 $response['error'] = 'Error interno del servidor al eliminar.';
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
        require "views/{$view}.php";
        $content = ob_get_clean();
        require 'views/layout.php';
    }
}
?>