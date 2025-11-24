<?php
// src/Egresos/Controllers/EgresoController.php

require_once __DIR__ . '/../Models/EgresoModel.php';
require_once __DIR__ . '/../../Categorias/Models/CategoriaModel.php';
require_once __DIR__ . '/../../Auditoria/Models/AuditoriaModel.php';
require_once __DIR__ . '/../../Presupuestos/Models/PresupuestoModel.php';

class EgresoController {
    private $db;
    private $egresoModel;
    private $categoriaModel;
    private $auditoriaModel;
    private $presupuestoModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->egresoModel = new EgresoModel($dbConnection);
        $this->categoriaModel = new CategoriaModel($dbConnection);
        $this->auditoriaModel = new AuditoriaModel($dbConnection);
        $this->presupuestoModel = new PresupuestoModel($dbConnection);
    }

    /**
     * Acción principal: Muestra la lista de egresos.
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
     */
    public function getCategoriasEgreso() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

        $categorias = $this->categoriaModel->getCategoriasByTipo('Egreso');
        echo json_encode($categorias);
        exit;
    }

    /**
     * Acción AJAX: Guarda un nuevo egreso o actualiza uno existente.
     */
    public function save() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $data = $_POST;
        // ¡IMPORTANTE! El trigger necesita el id_user, lo tomamos de la sesión
        $data['id_user'] = $_SESSION['user_id']; 
        
        $folio_egreso_id = $data['id'] ?? null;
        $isUpdate = !empty($folio_egreso_id);
        $response = ['success' => false];

        // --- Validación (igual que antes) ---
        if (empty($data['fecha']) || !isset($data['monto']) || $data['monto'] === '' || empty($data['id_categoria']) || empty($data['destinatario']) || empty($data['forma_pago'])) {
             $response['error'] = 'Los campos Fecha, Monto, Categoría, Destinatario y Forma de Pago son obligatorios.';
             echo json_encode($response); exit;
         }
         if (!is_numeric($data['monto']) || $data['monto'] <= 0) { $response['error'] = 'El monto debe ser un número positivo.'; echo json_encode($response); exit; }
         if (isset($data['activo_fijo']) && !in_array($data['activo_fijo'], ['SI', 'NO'])) { $response['error'] = 'Valor inválido para Activo Fijo.'; echo json_encode($response); exit; }
         $formas_validas = ['Efectivo', 'Transferencia', 'Cheque', 'Tarjeta D.', 'Tarjeta C.'];
         if (!in_array($data['forma_pago'], $formas_validas)) { $response['error'] = 'Forma de pago inválida.'; echo json_encode($response); exit; }
         if (!filter_var($data['id_categoria'], FILTER_VALIDATE_INT)) { $response['error'] = 'Categoría inválida.'; echo json_encode($response); exit; }
         $data['id_categoria'] = (int)$data['id_categoria'];
         // --- Fin Validación ---

        try {
            if (!$isUpdate) { // Crear
                // Validar límites de presupuesto: el id_presupuesto debe existir
                $selectedPres = isset($data['id_presupuesto']) ? (int)$data['id_presupuesto'] : 0;
                if ($selectedPres <= 0) { $response['error'] = 'Presupuesto inválido.'; echo json_encode($response); exit; }

                $presObj = $this->presupuestoModel->getPresupuestoById($selectedPres);
                if (!$presObj) { $response['error'] = 'Presupuesto no encontrado.'; echo json_encode($response); exit; }

                $montoNuevo = floatval($data['monto']);
                // Gastado actualmente en ese presupuesto
                $gastadoPres = $this->presupuestoModel->getGastadoEnPresupuesto($selectedPres);
                if (($gastadoPres + $montoNuevo) > floatval($presObj['monto_limite'])) {
                    $response['error'] = 'El monto excede el límite del presupuesto de la categoría.';
                    echo json_encode($response); exit;
                }

                // Si este presupuesto tiene parent_presupuesto, validar también contra el general
                $parentId = isset($presObj['parent_presupuesto']) ? (int)$presObj['parent_presupuesto'] : 0;
                if ($parentId > 0) {
                    // Gastado en el presupuesto general (suma de egresos asociados a ese general)
                    $gastadoGeneral = $this->presupuestoModel->getGastadoEnPresupuesto($parentId);
                    $parentObj = $this->presupuestoModel->getPresupuestoById($parentId);
                    $parentLim = floatval($parentObj['monto_limite'] ?? 0);
                    if (($gastadoGeneral + $montoNuevo) > $parentLim) {
                        $response['error'] = 'El monto excede el límite del Presupuesto General asociado.';
                        echo json_encode($response); exit;
                    }
                }

                $newId = $this->egresoModel->createEgreso($data);
                if ($newId) {
                    
                    // <--- ¡CORREGIDO! BORRAMOS LA LLAMADA A addAudit()
                    // El trigger 'trg_egresos_after_insert_aud' se encarga de esto.

                    // Si la BD no tiene triggers para egresos, añadir log desde PHP
                    if (!$this->auditoriaModel->hasTriggerForTable('egresos')) {
                        $det = 'Egreso creado (folio: ' . $newId . ')';
                        $this->auditoriaModel->addLog('Egreso', 'Insercion', $det, null, json_encode($data), $newId, null, $_SESSION['user_id'] ?? null);
                    }
                    $response['success'] = true;
                } else { $response['error'] = 'No se pudo crear el egreso.'; }
            } else { // Actualizar
                // intentar obtener datos antiguos para comparar
                $oldData = $this->egresoModel->getEgresoById($folio_egreso_id);
                $success = $this->egresoModel->updateEgreso($folio_egreso_id, $data);
                 if ($success) {
                    
                    // <--- ¡CORREGIDO! BORRAMOS LA LLAMADA A addAudit()
                    // El trigger 'trg_egresos_after_update' se encarga de esto.
                    
                    if (!$this->auditoriaModel->hasTriggerForTable('egresos')) {
                        $det = 'Egreso actualizado (folio: ' . $folio_egreso_id . ')';
                        $this->auditoriaModel->addLog('Egreso', 'Actualizacion', $det, json_encode($oldData), json_encode($data), $folio_egreso_id, null, $_SESSION['user_id'] ?? null);
                    }
                    $response['success'] = true;
                 } else { $response['error'] = 'No se pudo actualizar el egreso.'; }
            }
        } catch (Exception $e) {
            error_log("Error en EgresoController->save: " . $e->getMessage());
            // Devolver el mensaje de la excepción (ej: matrícula duplicada) si existe
            $response['error'] = $e->getMessage() ?: 'Error interno del servidor al guardar.';
        }

        echo json_encode($response);
        exit;
    }

    /**
     * Acción AJAX: Obtiene los datos de un egreso específico por su ID (folio_egreso).
     * (Sin cambios necesarios aquí)
     */
     public function getEgresoData() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }
        $folio_egreso_id = $_GET['id'] ?? 0;
        $egreso = $this->egresoModel->getEgresoById($folio_egreso_id);
        echo json_encode($egreso ?: ['error' => 'Egreso no encontrado.']);
        exit;
     }

    /**
     * Acción AJAX: Elimina un egreso usando su PK (folio_egreso).
     */
    public function delete() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $folio_egreso_id = $_POST['id'] ?? 0;
        $response = ['success' => false];

        if ($folio_egreso_id > 0) {
            try {
                    // Obtener datos anteriores para el log si no hay trigger
                    $oldData = $this->egresoModel->getEgresoById($folio_egreso_id);
                    $success = $this->egresoModel->deleteEgreso($folio_egreso_id);
                if ($success) {
                        if (!$this->auditoriaModel->hasTriggerForTable('egresos')) {
                            $det = 'Egreso eliminado (folio: ' . $folio_egreso_id . ')';
                            $this->auditoriaModel->addLog('Egreso', 'Eliminacion', $det, json_encode($oldData), null, $folio_egreso_id, null, $_SESSION['user_id'] ?? null);
                        }
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
     * Acción AJAX: Obtiene datos para gráfica de egresos por categoría
     */
    public function getGraficaEgresosPorCategoria() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { 
            echo json_encode(['success' => false, 'error' => 'No autorizado']); 
            exit; 
        }

        try {
            $query = "SELECT c.nombre, COALESCE(SUM(e.monto), 0) as total
                     FROM categorias c
                     LEFT JOIN egresos e ON e.id_categoria = c.id_categoria
                     WHERE c.tipo = 'Egreso'
                     GROUP BY c.id_categoria, c.nombre
                     HAVING total > 0
                     ORDER BY total DESC";
            
            $result = $this->db->query($query);
            $categorias = [];
            $montos = [];
            
            while ($row = $result->fetch_assoc()) {
                $categorias[] = $row['nombre'];
                $montos[] = floatval($row['total']);
            }
            
            echo json_encode([
                'success' => true,
                'categorias' => $categorias,
                'montos' => $montos
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

    /**
     * Acción AJAX: Obtiene datos para gráfica de egresos por mes (últimos 6 meses)
     */
    public function getGraficaEgresosPorMes() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { 
            echo json_encode(['success' => false, 'error' => 'No autorizado']); 
            exit; 
        }

        try {
            $meses = [];
            $montos = [];
            
            for ($i = 5; $i >= 0; $i--) {
                $mes = date('Y-m', strtotime("-$i months"));
                $nombreMes = date('M Y', strtotime("-$i months"));
                
                $query = "SELECT COALESCE(SUM(monto), 0) as total 
                         FROM egresos 
                         WHERE DATE_FORMAT(fecha, '%Y-%m') = ?";
                $stmt = $this->db->prepare($query);
                $stmt->bind_param('s', $mes);
                $stmt->execute();
                $total = $stmt->get_result()->fetch_assoc()['total'];
                $stmt->close();
                
                $meses[] = $nombreMes;
                $montos[] = floatval($total);
            }
            
            echo json_encode([
                'success' => true,
                'meses' => $meses,
                'montos' => $montos
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
        exit;
    }

     /**
     * Función helper para renderizar vistas.
     * (Sin cambios)
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
} // Fin de la clase EgresoController
?>