<?php
// controllers/PresupuestoController.php

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
        $id = $data['id'] ?? null; // ID puede venir si es una edición explícita
        $response = ['success' => false];

        // Validación básica
        if (empty($data['categoria']) || empty($data['monto']) || empty($data['fecha'])) {
             $response['error'] = 'Todos los campos son obligatorios.';
             echo json_encode($response);
             exit;
         }
         if (!is_numeric($data['monto']) || $data['monto'] <= 0) {
             $response['error'] = 'El monto debe ser un número positivo.';
             echo json_encode($response);
             exit;
         }

        try {
            // El modelo se encarga de decidir si crear o actualizar basado en la categoría
            $success = $this->presupuestoModel->savePresupuesto($data, $id);

            if ($success) {
                // Determinar si fue creación o actualización para la auditoría
                // (Podríamos mejorar esto consultando antes/después, pero savePresupuesto no devuelve esa info)
                $action = !empty($id) ? 'Actualización' : 'Creación/Actualización';
                addAudit($this->db, 'Presupuesto', $action, "Presupuesto para {$data['categoria']}");
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
                 // Opcional: obtener datos antes de borrar para auditoría
                 $presupuesto = $this->presupuestoModel->getPresupuestoById($id);
                 $catNombre = $presupuesto ? $presupuesto['categoria'] : "ID {$id}";

                $success = $this->presupuestoModel->deletePresupuesto($id);
                if ($success) {
                    addAudit($this->db, 'Presupuesto', 'Eliminación', "Presupuesto para {$catNombre}");
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