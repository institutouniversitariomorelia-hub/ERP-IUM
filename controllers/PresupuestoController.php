<?php
// controllers/PresupuestoController.php (CORREGIDO)

require_once 'models/PresupuestoModel.php';
require_once 'models/CategoriaModel.php'; // Necesitamos las categorías

class PresupuestoController {
    private $db;
    private $presupuestoModel;
    private $categoriaModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->presupuestoModel = new PresupuestoModel($dbConnection);
        $this->categoriaModel = new CategoriaModel($dbConnection);
    }

    /**
     * Acción principal: Muestra la lista de presupuestos.
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login'); exit; }

        $presupuestos = $this->presupuestoModel->getAllPresupuestos();

        $pageTitle = "Presupuestos";
        $activeModule = "presupuestos";

        $this->renderView('presupuestos_list', [
            'pageTitle' => $pageTitle,
            'activeModule' => $activeModule,
            'presupuestos' => $presupuestos
        ]);
    }

    /**
     * Acción AJAX: Guarda/Actualiza un presupuesto.
     */
    public function save() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $data = $_POST;
        // <-- ¡CORRECCIÓN 1: AÑADIR EL ID DE USUARIO DE LA SESIÓN!
        $data['id_user'] = $_SESSION['user_id'];
        
        $id = $data['id'] ?? null; // ID puede venir si es una edición (id_presupuesto)
        $response = ['success' => false];

        // Validación básica (ajustada a nuestra BD)
        if (empty($data['monto_limite']) || empty($data['fecha'])) {
             $response['error'] = 'El monto límite y la fecha son obligatorios.';
             echo json_encode($response);
             exit;
         }
         if (!is_numeric($data['monto_limite']) || $data['monto_limite'] <= 0) {
             $response['error'] = 'El monto debe ser un número positivo.';
             echo json_encode($response);
             exit;
         }

        try {
            // El modelo ahora hace un simple INSERT o UPDATE
            $success = $this->presupuestoModel->savePresupuesto($data, $id);

            if ($success) {
                // <-- ¡CORRECCIÓN 2: BORRAMOS LA LLAMADA A addAudit()!
                // Los triggers 'trg_presupuestos_after_insert_aud' y '..._after_update'
                // se encargan de esto.

                $response['success'] = true;
            } else {
                 $response['error'] = 'No se pudo guardar el presupuesto en la base de datos.';
            }
        } catch (Exception $e) {
            error_log("Error en PresupuestoController->save: " . $e->getMessage());
            $response['error'] = $e->getMessage() ?: 'Error interno del servidor al guardar.';
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Acción AJAX: Obtiene los datos de un presupuesto específico (para editar).
     */
     public function getPresupuestoData() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

        $id = $_GET['id'] ?? 0;
        $presupuesto = $this->presupuestoModel->getPresupuestoById($id);

        if ($presupuesto) {
            echo json_encode($presupuesto);
        } else {
             echo json_encode(['error' => 'Presupuesto no encontrado.']);
        }
        exit;
     }
     
     /**
      * Acción AJAX: Obtiene todas las categorías (para llenar el select del modal).
      * NOTA: Esto es para un formulario, lo dejamos aunque la lógica del modelo cambió.
      */
      public function getAllCategorias() {
           header('Content-Type: application/json');
           if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }
           
           $categorias = $this->categoriaModel->getAllCategorias();
           echo json_encode($categorias);
           exit;
      }

    /**
     * Acción AJAX: Elimina un presupuesto.
     */
    public function delete() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $id = $_POST['id'] ?? 0;
        $response = ['success' => false];

        if ($id > 0) {
            try {
                // No necesitamos $presupuesto, el trigger lo hace.
                $success = $this->presupuestoModel->deletePresupuesto($id);
                if ($success) {
                    
                    // <-- ¡CORRECCIÓN 3: BORRAMOS LA LLAMADA A addAudit()!
                    // Vamos a crear el trigger 'trg_presupuestos_before_delete'
                    // para que esto sea automático.

                    $response['success'] = true;
                } else {
                    $response['error'] = 'No se pudo eliminar el presupuesto de la base de datos.';
                }
            } catch (Exception $e) {
                 error_log("Error en PresupuestoController->delete: " . $e->getMessage());
                 $response['error'] = 'Error interno del servidor al eliminar.';
            }
        } else {
            $response['error'] = 'ID de presupuesto inválido.';
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