<?php
// controllers/EgresoController.php

require_once 'models/EgresoModel.php';
require_once 'models/CategoriaModel.php'; // Asegúrate que CategoriaModel está completo y correcto

class EgresoController {
    private $db;
    private $egresoModel;
    private $categoriaModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->egresoModel = new EgresoModel($dbConnection);
        $this->categoriaModel = new CategoriaModel($dbConnection);
    }

    /**
     * Acción principal: Muestra la lista de egresos.
     * (No necesita cambios funcionales aquí)
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login'); exit; }

        $egresos = $this->egresoModel->getAllEgresos();

        $pageTitle = "Egresos";
        $activeModule = "egresos";

        $this->renderView('egresos_list', [
            'pageTitle' => $pageTitle,
            'activeModule' => $activeModule,
            'egresos' => $egresos
        ]);
    }

    /**
     * Acción AJAX: Obtiene las categorías de tipo 'Egreso' (ID y Nombre).
     * Usada para llenar el <select> en el modal.
     */
    public function getCategoriasEgreso() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

        // Llama al método del modelo que devuelve todas las columnas (incluyendo id)
        $categorias = $this->categoriaModel->getCategoriasByTipo('Egreso');
        echo json_encode($categorias);
        exit;
    }

    /**
     * Acción AJAX: Guarda un nuevo egreso o actualiza uno existente (CON CAMPOS NUEVOS).
     */
    public function save() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $data = $_POST;
        // **IMPORTANTE**: Usar el ID de usuario de la sesión, MÁS SEGURO
        $data['id_user'] = $_SESSION['user_id'];

        // El ID del egreso (folio_egreso) viene en $data['id'] si es una actualización
        $folio_egreso_id = $data['id'] ?? null;
        $isUpdate = !empty($folio_egreso_id);
        $response = ['success' => false];

        // Validación más específica según tu BD (campos NOT NULL)
        if (empty($data['fecha']) || empty($data['monto']) || empty($data['id_categoria']) || empty($data['destinatario']) || empty($data['forma_pago'])) {
             $response['error'] = 'Los campos Fecha, Monto, Categoría, Destinatario y Forma de Pago son obligatorios.';
             echo json_encode($response);
             exit;
         }
         // Validar monto
         if (!is_numeric($data['monto']) || $data['monto'] <= 0) {
              $response['error'] = 'El monto debe ser un número positivo.';
              echo json_encode($response);
              exit;
          }
         // Validar activo_fijo (enum SI/NO)
         if (isset($data['activo_fijo']) && !in_array($data['activo_fijo'], ['SI', 'NO'])) {
             $response['error'] = 'Valor inválido para Activo Fijo (Debe ser SI o NO).';
             echo json_encode($response);
             exit;
         }
         // Validar forma_pago (enum)
         $formas_validas = ['Efectivo', 'Transferencia', 'Cheque', 'Tarjeta D.', 'Tarjeta C.'];
          if (!in_array($data['forma_pago'], $formas_validas)) {
              $response['error'] = 'Forma de pago inválida.';
              echo json_encode($response);
              exit;
          }
          // Asegurarse de que id_categoria es un número entero
          if (!isset($data['id_categoria']) || !filter_var($data['id_categoria'], FILTER_VALIDATE_INT)) {
                $response['error'] = 'Debe seleccionar una categoría válida.';
                echo json_encode($response);
                exit;
          }
          $data['id_categoria'] = (int)$data['id_categoria']; // Convertir a entero


        try {
            if (!$isUpdate) { // Crear
                // Llamar a createEgreso del modelo
                $newId = $this->egresoModel->createEgreso($data);
                if ($newId) { // createEgreso ahora devuelve el ID si tiene éxito
                    addAudit($this->db, 'Egreso', 'Creación', "Egreso creado ID: {$newId} para {$data['destinatario']}");
                    $response['success'] = true;
                } else {
                     $response['error'] = 'No se pudo crear el egreso en la base de datos.';
                }
            } else { // Actualizar
                // Pasamos el folio_egreso_id (PK) y los datos
                $success = $this->egresoModel->updateEgreso($folio_egreso_id, $data);
                 if ($success) {
                     addAudit($this->db, 'Egreso', 'Actualización', "Egreso ID: {$folio_egreso_id}");
                     $response['success'] = true;
                 } else {
                     $response['error'] = 'No se pudo actualizar el egreso en la base de datos.';
                 }
            }
        } catch (Exception $e) {
            error_log("Error en EgresoController->save: " . $e->getMessage());
            $response['error'] = 'Error interno del servidor al guardar. Consulte el log.';
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Acción AJAX: Obtiene los datos de un egreso específico por su ID (folio_egreso).
     */
     public function getEgresoData() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

        // Recibe el ID del egreso (folio_egreso) como 'id' desde JS
        $folio_egreso_id = $_GET['id'] ?? 0;
        $egreso = $this->egresoModel->getEgresoById($folio_egreso_id); // El modelo busca por PK

        if ($egreso) {
            // Asegurarse de devolver todos los campos necesarios para el formulario
            echo json_encode($egreso);
        } else {
             echo json_encode(['error' => 'Egreso no encontrado.']);
        }
        exit;
     }

    /**
     * Acción AJAX: Elimina un egreso usando su PK (folio_egreso).
     */
    public function delete() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        // Recibe el ID del egreso (folio_egreso) como 'id' desde JS
        $folio_egreso_id = $_POST['id'] ?? 0;
        $response = ['success' => false];

        if ($folio_egreso_id > 0) {
            try {
                $success = $this->egresoModel->deleteEgreso($folio_egreso_id); // Pasa la PK correcta
                if ($success) {
                    addAudit($this->db, 'Egreso', 'Eliminación', "Egreso ID {$folio_egreso_id}");
                    $response['success'] = true;
                } else {
                    $response['error'] = 'No se pudo eliminar el egreso de la base de datos.';
                }
            } catch (Exception $e) {
                 error_log("Error en EgresoController->delete: " . $e->getMessage());
                 $response['error'] = 'Error interno del servidor al eliminar.';
            }
        } else {
            $response['error'] = 'ID de egreso inválido.';
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
         // Verificar si el archivo de vista existe
         if (file_exists("views/{$view}.php")) {
            require "views/{$view}.php";
        } else {
            error_log("Vista no encontrada: views/{$view}.php");
            echo "<div class='alert alert-danger'>Error: No se encontró la plantilla de la vista '{$view}'.</div>";
        }
        $content = ob_get_clean();
        require 'views/layout.php';
    }
} // Fin de la clase EgresoController

// Ya no necesitamos crear CategoriaModel aquí, asumimos que existe
?>