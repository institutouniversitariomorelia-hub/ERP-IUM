<?php
// controllers/CategoriaController.php

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
                if ($success) addAudit($this->db, 'Categoria', 'Creación', "Categoría {$data['nombre']}");
            } else { // Actualizar
                $success = $this->categoriaModel->updateCategoria($id, $data);
                 if ($success) addAudit($this->db, 'Categoria', 'Actualización', "Categoría {$data['nombre']} (ID: {$id})");
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
        $categoria = $this->categoriaModel->getCategoriaById($id);

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
                // Opcional: Obtener nombre antes de borrar para auditoría
                $categoria = $this->categoriaModel->getCategoriaById($id);
                $nombreCat = $categoria ? $categoria['nombre'] : "ID {$id}";

                $success = $this->categoriaModel->deleteCategoria($id);
                if ($success) {
                    addAudit($this->db, 'Categoria', 'Eliminación', "Categoría {$nombreCat}");
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