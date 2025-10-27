<?php
// controllers/IngresoController.php

require_once 'models/IngresoModel.php';
require_once 'models/CategoriaModel.php'; // También usa categorías

class IngresoController {
    private $db;
    private $ingresoModel;
    private $categoriaModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->ingresoModel = new IngresoModel($dbConnection);
        $this->categoriaModel = new CategoriaModel($dbConnection);
    }

    /**
     * Acción principal: Muestra la lista de ingresos.
     * (No necesita cambios funcionales aquí)
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login'); exit; }

        $ingresos = $this->ingresoModel->getAllIngresos();

        $pageTitle = "Ingresos";
        $activeModule = "ingresos";

        $this->renderView('ingresos_list', [
            'pageTitle' => $pageTitle,
            'activeModule' => $activeModule,
            'ingresos' => $ingresos
        ]);
    }

    /**
     * Acción AJAX: Obtiene las categorías de tipo 'Ingreso' (ID y Nombre).
     * Usada para llenar el <select> en el modal.
     */
    public function getCategoriasIngreso() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

        // Llama al método del modelo que devuelve todas las columnas (incluyendo id)
        $categorias = $this->categoriaModel->getCategoriasByTipo('Ingreso');
        echo json_encode($categorias);
        exit;
    }

    /**
     * Acción AJAX: Guarda un nuevo ingreso o actualiza uno existente (CON CAMPOS NUEVOS).
     */
    public function save() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $data = $_POST;
        // El ID del ingreso (folio_ingreso) viene en $data['id'] si es una actualización
        $folio_ingreso_id = $data['id'] ?? null;
        $isUpdate = !empty($folio_ingreso_id);
        $response = ['success' => false];

        // Validación más completa según la estructura de la tabla ingresos
        $requiredFields = ['fecha', 'alumno', 'matricula', 'nivel', 'monto', 'metodo_de_pago', 'concepto', 'año', 'programa', 'id_categoria'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $response['error'] = "El campo '" . ucfirst(str_replace('_', ' ', $field)) . "' es obligatorio.";
                echo json_encode($response);
                exit;
            }
        }
        // Validaciones específicas
        if (!is_numeric($data['monto']) || $data['monto'] <= 0) { $response['error'] = 'El monto debe ser un número positivo.'; echo json_encode($response); exit; }
        if (!filter_var($data['año'], FILTER_VALIDATE_INT) || $data['año'] < 2000 || $data['año'] > 2100) { $response['error'] = 'El año debe ser válido (ej: 2025).'; echo json_encode($response); exit; }
        if (isset($data['dia_pago']) && $data['dia_pago'] !== '' && (!filter_var($data['dia_pago'], FILTER_VALIDATE_INT) || $data['dia_pago'] < 1 || $data['dia_pago'] > 31)) { $response['error'] = 'El día de pago debe ser un número entre 1 y 31.'; echo json_encode($response); exit; }
        if (isset($data['grado']) && $data['grado'] !== '' && (!filter_var($data['grado'], FILTER_VALIDATE_INT) || $data['grado'] < 1 || $data['grado'] > 15)) { $response['error'] = 'El grado debe ser un número válido.'; echo json_encode($response); exit; }
        $niveles_validos = ['Licenciatura','Maestría','Doctorado'];
        if (!in_array($data['nivel'], $niveles_validos)) { $response['error'] = 'Nivel académico inválido.'; echo json_encode($response); exit; }
        $metodos_validos = ['Efectivo','Transferencia','Depósito'];
        if (!in_array($data['metodo_de_pago'], $metodos_validos)) { $response['error'] = 'Método de pago inválido.'; echo json_encode($response); exit; }
        $conceptos_validos = ['Inscripción','Reinscripción','Titulación','Colegiatura','Constancia simple','Constancia con calificaciones','Historiales','Certificados','Equivalencias','Credenciales','Otros'];
        if (!in_array($data['concepto'], $conceptos_validos)) { $response['error'] = 'Concepto inválido.'; echo json_encode($response); exit; }
        $modalidades_validas = ['Cuatrimestral','Semestral', null, '']; // Aceptar vacío o null
        if (isset($data['modalidad']) && !in_array($data['modalidad'], $modalidades_validas, true)) { $response['error'] = 'Modalidad inválida.'; echo json_encode($response); exit; }


        try {
            if (!$isUpdate) { // Crear
                // Llamar a createIngreso del modelo
                $newId = $this->ingresoModel->createIngreso($data);
                if ($newId) {
                    addAudit($this->db, 'Ingreso', 'Creación', "Ingreso creado ID: {$newId} para {$data['alumno']} ({$data['matricula']})");
                    $response['success'] = true;
                    $response['newId'] = $newId; // Devolver el nuevo ID por si JS lo necesita
                } else {
                     $response['error'] = 'No se pudo crear el ingreso en la base de datos.';
                }
            } else { // Actualizar
                // Pasamos el folio_ingreso_id (PK) y los datos
                $success = $this->ingresoModel->updateIngreso($folio_ingreso_id, $data);
                 if ($success) {
                     addAudit($this->db, 'Ingreso', 'Actualización', "Ingreso ID: {$folio_ingreso_id}");
                     $response['success'] = true;
                 } else {
                     $response['error'] = 'No se pudo actualizar el ingreso en la base de datos.';
                 }
            }
        } catch (Exception $e) {
            // Capturar excepción específica de matrícula duplicada
            if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), "'matricula'") !== false) {
                 $response['error'] = "La matrícula '{$data['matricula']}' ya existe.";
            } else {
                 error_log("Error en IngresoController->save: " . $e->getMessage());
                 $response['error'] = 'Error interno del servidor al guardar. Consulte el log.';
            }
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Acción AJAX: Obtiene los datos de un ingreso específico por su ID (folio_ingreso).
     */
     public function getIngresoData() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

        // Recibe el ID del ingreso (folio_ingreso) como 'id' desde JS
        $folio_ingreso_id = $_GET['id'] ?? 0;
        $ingreso = $this->ingresoModel->getIngresoById($folio_ingreso_id); // El modelo busca por PK

        if ($ingreso) {
            // Asegurarse de devolver todos los campos necesarios para el formulario
            echo json_encode($ingreso);
        } else {
             echo json_encode(['error' => 'Ingreso no encontrado.']);
        }
        exit;
     }

    /**
     * Acción AJAX: Elimina un ingreso usando su PK (folio_ingreso).
     */
    public function delete() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        // Recibe el ID del ingreso (folio_ingreso) como 'id' desde JS
        $folio_ingreso_id = $_POST['id'] ?? 0;
        $response = ['success' => false];

        if ($folio_ingreso_id > 0) {
            try {
                $success = $this->ingresoModel->deleteIngreso($folio_ingreso_id); // Pasa la PK correcta
                if ($success) {
                    addAudit($this->db, 'Ingreso', 'Eliminación', "Ingreso ID {$folio_ingreso_id}");
                    $response['success'] = true;
                } else {
                    $response['error'] = 'No se pudo eliminar el ingreso de la base de datos.';
                }
            } catch (Exception $e) {
                 error_log("Error en IngresoController->delete: " . $e->getMessage());
                 $response['error'] = 'Error interno del servidor al eliminar.';
            }
        } else {
            $response['error'] = 'ID de ingreso inválido.';
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
} // Fin de la clase IngresoController
?>