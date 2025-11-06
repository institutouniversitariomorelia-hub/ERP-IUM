<?php
// controllers/PresupuestoController.php (CORREGIDO)

require_once 'models/PresupuestoModel.php';
require_once 'models/CategoriaModel.php'; // Necesitamos las categorías
require_once 'models/AuditoriaModel.php';

class PresupuestoController {
    private $db;
    private $presupuestoModel;
    private $categoriaModel;
    private $auditoriaModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->presupuestoModel = new PresupuestoModel($dbConnection);
        $this->categoriaModel = new CategoriaModel($dbConnection);
        $this->auditoriaModel = new AuditoriaModel($dbConnection);
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
        // El formulario envía el campo como 'monto' (name="monto") — mapear a 'monto_limite' para el modelo
        if (isset($data['monto'])) {
            $data['monto_limite'] = $data['monto'];
        }

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
                // Si no existen triggers en la BD para presupuestos, hacemos fallback en PHP
                if (!$this->auditoriaModel->hasTriggerForTable('presupuestos')) {
                    $det = 'Presupuesto ' . (empty($id) ? 'creado' : 'actualizado') . ': ' . ($data['descripcion'] ?? '') . ' monto: ' . ($data['monto_limite'] ?? '');
                    $this->auditoriaModel->addLog('Presupuesto', empty($id) ? 'Insercion' : 'Actualizacion', $det, null, json_encode($data), null, null, $_SESSION['user_id'] ?? null);
                }
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
      * Acción AJAX: Devuelve todos los presupuestos disponibles (ID y monto) en JSON.
      * Usado por el frontend para permitir asignar un presupuesto a un egreso.
      */
     public function getAllPresupuestos() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

         $pres = $this->presupuestoModel->getAllPresupuestos();
         // Normalizar salida: id, monto_limite, fecha
         $out = [];
         foreach ($pres as $p) {
             $out[] = [
                 'id' => $p['id_presupuesto'] ?? ($p['id'] ?? null),
                 'monto_limite' => $p['monto_limite'] ?? ($p['monto'] ?? null),
                 'fecha' => $p['fecha'] ?? null
             ];
         }
         echo json_encode($out);
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
                    if (!$this->auditoriaModel->hasTriggerForTable('presupuestos')) {
                        $det = 'Presupuesto eliminado (id: ' . $id . ')';
                        $this->auditoriaModel->addLog('Presupuesto', 'Eliminacion', $det, null, null, null, null, $_SESSION['user_id'] ?? null);
                    }
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