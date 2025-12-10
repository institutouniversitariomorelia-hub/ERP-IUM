<?php
// src/Ingresos/Controllers/IngresoController.php

require_once __DIR__ . '/../Models/IngresoModel.php';
require_once __DIR__ . '/../../Categorias/Models/CategoriaModel.php';
require_once __DIR__ . '/../../Auditoria/Models/AuditoriaModel.php';

class IngresoController {
    private $db;
    private $ingresoModel;
    private $categoriaModel;
    private $auditoriaModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->ingresoModel = new IngresoModel($dbConnection);
        $this->categoriaModel = new CategoriaModel($dbConnection);
        $this->auditoriaModel = new AuditoriaModel($dbConnection);
    }

    /**
     * Acción principal: Muestra la lista de ingresos.
     * (No necesita cambios funcionales aquí)
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login'); exit; }

        // Por defecto mostramos solo ingresos activos (estatus = 1). Si viene ?ver_reembolsos=1, mostramos solo estatus = 0
        $showReembolsos = isset($_GET['ver_reembolsos']) && $_GET['ver_reembolsos'] == '1';
        if ($showReembolsos) {
            $ingresos = $this->ingresoModel->getIngresosByStatus(0);
        } else {
            $ingresos = $this->ingresoModel->getIngresosByStatus(1);
        }

        $pageTitle = "Ingresos";
        $activeModule = "ingresos";

        $this->renderView('ingresos_list', [
            'pageTitle' => $pageTitle,
            'activeModule' => $activeModule,
            'ingresos' => $ingresos,
            'show_reembolsos' => $showReembolsos
        ]);
    }

    /**
     * Acción AJAX: Genera un egreso a partir de un ingreso y marca el ingreso como reembolsado (transacción).
     * Espera POST: id (folio_ingreso), opcionales: forma_pago, descripcion
     */
    public function reembolsar() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $folio = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($folio <= 0) { echo json_encode(['success' => false, 'error' => 'ID de ingreso inválido']); exit; }

        // Obtener ingreso
        $ingreso = $this->ingresoModel->getIngresoById($folio);
        if (!$ingreso) { echo json_encode(['success' => false, 'error' => 'Ingreso no encontrado']); exit; }

        // Buscar subpresupuesto hijo de 9999 con id_categoria = 224
        $stmt = $this->db->prepare("SELECT id_presupuesto FROM presupuestos WHERE parent_presupuesto = ? AND id_categoria = ? LIMIT 1");
        if (!$stmt) { echo json_encode(['success' => false, 'error' => 'Error interno (presupuesto)']); exit; }
        $parent = 10; $catReembolso = 21;
        $stmt->bind_param('ii', $parent, $catReembolso);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();
        $id_presupuesto = $row['id_presupuesto'] ?? null;

        // Datos para el egreso
        $egresoData = [
            'proveedor' => 'Reembolsos',
            'descripcion' => isset($_POST['descripcion']) ? trim($_POST['descripcion']) : 'Reembolso por ingreso ' . $folio,
            'monto' => $ingreso['monto'],
            'fecha' => date('Y-m-d'),
            'destinatario' => $ingreso['alumno'] ?? 'N/A',
            'forma_pago' => isset($_POST['forma_pago']) ? $_POST['forma_pago'] : 'Efectivo',
            'documento_de_amparo' => 'Recibo de ingreso #' . $folio,
            'id_user' => $_SESSION['user_id'],
            'id_categoria' => 224
        ];
        if ($id_presupuesto) $egresoData['id_presupuesto'] = (int)$id_presupuesto;

        // Ejecutar transacción: insertar egreso y marcar ingreso
        try {
            $this->db->begin_transaction();
            $newEgresoId = $this->egresoModel->createEgreso($egresoData);
            if (!$newEgresoId) throw new Exception('No se pudo crear el egreso');

            $ok = $this->ingresoModel->markAsReembolsado($folio);
            if (!$ok) throw new Exception('No se pudo actualizar el estatus del ingreso');

            // Auditoría (si la BD no tiene trigger)
            if (!$this->auditoriaModel->hasTriggerForTable('ingresos')) {
                $this->auditoriaModel->addLog('Ingreso', 'Actualizacion', 'Ingreso marcado como reembolsado', null, null, null, $folio, $_SESSION['user_id'] ?? null);
            }
            if (!$this->auditoriaModel->hasTriggerForTable('egresos')) {
                $this->auditoriaModel->addLog('Egreso', 'Insercion', 'Egreso creado por reembolso (folio ingreso ' . $folio . ')', null, json_encode($egresoData), $newEgresoId, null, $_SESSION['user_id'] ?? null);
            }

            $this->db->commit();
            echo json_encode(['success' => true, 'folio_egreso' => $newEgresoId]);
            exit;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Error en reembolsar: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
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
     * Acción AJAX: Guarda un nuevo ingreso o actualiza uno existente (CON PAGOS DIVIDIDOS).
     */
    public function save() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $data = $_POST;
        
        // El ID del ingreso (folio_ingreso) viene en $data['id'] si es una actualización
        $folio_ingreso_id = $data['id'] ?? null;
        $isUpdate = !empty($folio_ingreso_id);
        $response = ['success' => false];

        // Validación de campos obligatorios
        $requiredFields = ['fecha', 'alumno', 'matricula', 'nivel', 'monto', 'metodo_de_pago', 'anio', 'programa', 'id_categoria'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $response['error'] = "El campo '" . ucfirst(str_replace('_', ' ', $field)) . "' es obligatorio.";
                echo json_encode($response);
                exit;
            }
        }
        
        // Validaciones específicas
        if (!is_numeric($data['monto']) || $data['monto'] <= 0) { $response['error'] = 'El monto debe ser un número positivo.'; echo json_encode($response); exit; }
        if (!isset($data['anio']) || !filter_var($data['anio'], FILTER_VALIDATE_INT) || $data['anio'] < 2000 || $data['anio'] > 2100) { $response['error'] = 'El año debe ser válido (ej: 2025).'; echo json_encode($response); exit; }
        if (isset($data['grado']) && $data['grado'] !== '' && (!filter_var($data['grado'], FILTER_VALIDATE_INT) || $data['grado'] < 1 || $data['grado'] > 15)) { $response['error'] = 'El grado debe ser un número válido.'; echo json_encode($response); exit; }
        
        $niveles_validos = ['Licenciatura','Maestría','Doctorado'];
        if (!in_array($data['nivel'], $niveles_validos)) { $response['error'] = 'Nivel académico inválido.'; echo json_encode($response); exit; }
        
        $metodos_validos = ['Efectivo','Transferencia','Depósito','Tarjeta Débito','Tarjeta Crédito','Mixto'];
        if (!in_array($data['metodo_de_pago'], $metodos_validos)) { $response['error'] = 'Método de pago inválido.'; echo json_encode($response); exit; }
        
        $modalidades_validas = ['Cuatrimestral','Semestral', null, ''];
        if (isset($data['modalidad']) && !in_array($data['modalidad'], $modalidades_validas, true)) { $response['error'] = 'Modalidad inválida.'; echo json_encode($response); exit; }

        // Validar y procesar pagos parciales
        $pagos = [];
        if (isset($data['pagos']) && !empty($data['pagos'])) {
            $pagosJson = json_decode($data['pagos'], true);
            if ($pagosJson && is_array($pagosJson)) {
                $sumaPagos = 0;
                foreach ($pagosJson as $pago) {
                    if (!isset($pago['metodo']) || !isset($pago['monto'])) {
                        $response['error'] = 'Cada pago debe tener método y monto.';
                        echo json_encode($response);
                        exit;
                    }
                    $sumaPagos += floatval($pago['monto']);
                    $pagos[] = $pago;
                }
                
                // Validar que la suma de pagos coincida con el monto total
                $diferencia = abs(floatval($data['monto']) - $sumaPagos);
                if ($diferencia >= 0.01) {
                    $response['error'] = "La suma de pagos parciales ($sumaPagos) no coincide con el monto total ({$data['monto']}).";
                    echo json_encode($response);
                    exit;
                }
            }
        }

        try {
            if (!$isUpdate) { // Crear
                $newId = $this->ingresoModel->createIngreso($data);
                if ($newId) {
                    // Guardar pagos parciales si existen
                    if (!empty($pagos)) {
                        $this->ingresoModel->savePagosParciales($newId, $pagos);
                    }
                    
                    if (!$this->auditoriaModel->hasTriggerForTable('ingresos')) {
                        $det = 'Ingreso creado (folio: ' . $newId . ')';
                        $this->auditoriaModel->addLog('Ingreso', 'Insercion', $det, null, json_encode($data), null, $newId, $_SESSION['user_id'] ?? null);
                    }
                    $response['success'] = true;
                    $response['newId'] = $newId;
                } else {
                     $response['error'] = 'No se pudo crear el ingreso en la base de datos.';
                }
            } else { // Actualizar
                $oldData = $this->ingresoModel->getIngresoById($folio_ingreso_id);
                $success = $this->ingresoModel->updateIngreso($folio_ingreso_id, $data);
                 if ($success) {
                    // Actualizar pagos parciales
                    if (!empty($pagos)) {
                        $this->ingresoModel->savePagosParciales($folio_ingreso_id, $pagos);
                    }
                    
                    if (!$this->auditoriaModel->hasTriggerForTable('ingresos')) {
                        $det = 'Ingreso actualizado (folio: ' . $folio_ingreso_id . ')';
                        $this->auditoriaModel->addLog('Ingreso', 'Actualizacion', $det, json_encode($oldData), json_encode($data), null, $folio_ingreso_id, $_SESSION['user_id'] ?? null);
                    }
                    $response['success'] = true;
                 } else {
                     $response['error'] = 'No se pudo actualizar el ingreso en la base de datos.';
                 }
            }
        } catch (Exception $e) {
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
     * Acción AJAX: Obtiene los datos de un ingreso específico por su ID (folio_ingreso) CON PAGOS PARCIALES.
     */
     public function getIngresoData() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }

        $folio_ingreso_id = $_GET['id'] ?? 0;
        $ingreso = $this->ingresoModel->getIngresoById($folio_ingreso_id);

        if ($ingreso) {
            // Obtener pagos parciales si existen
            $pagosParciales = $this->ingresoModel->getPagosParciales($folio_ingreso_id);
            $ingreso['pagos_parciales'] = $pagosParciales;
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
                $oldData = $this->ingresoModel->getIngresoById($folio_ingreso_id);
                $success = $this->ingresoModel->deleteIngreso($folio_ingreso_id); // Pasa la PK correcta
                if ($success) {
                    if (!$this->auditoriaModel->hasTriggerForTable('ingresos')) {
                        $oldValor = $oldData ? json_encode($oldData) : null;
                        $this->auditoriaModel->addLog('Ingreso', 'Eliminacion', null, $oldValor, null, null, $folio_ingreso_id, $_SESSION['user_id'] ?? null);
                    }
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
     * Acción AJAX: Obtiene datos para gráfica de ingresos por categoría
     */
    public function getGraficaIngresosPorCategoria() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { 
            echo json_encode(['success' => false, 'error' => 'No autorizado']); 
            exit; 
        }

        try {
            $query = "SELECT c.nombre, COALESCE(SUM(i.monto), 0) as total
                     FROM categorias c
                     LEFT JOIN ingresos i ON i.id_categoria = c.id_categoria AND i.estatus = 1
                     WHERE c.tipo = 'Ingreso'
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
     * Acción AJAX: Obtiene datos para gráfica de ingresos por mes (últimos 6 meses)
     */
    public function getGraficaIngresosPorMes() {
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
                         FROM ingresos 
                         WHERE DATE_FORMAT(fecha, '%Y-%m') = ? AND estatus = 1";
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
     */
    protected function renderView($view, $data = []) {
        extract($data);
        ob_start();
         // Verificar si el archivo de vista existe
         if (file_exists(__DIR__ . "/../Views/{$view}.php")) {
             require __DIR__ . "/../Views/{$view}.php";
         } else {
             error_log("Vista no encontrada: " . __DIR__ . "/../Views/{$view}.php");
             echo "<div class='alert alert-danger'>Error: No se encontró la plantilla de la vista '{$view}'.</div>";
        }
        $content = ob_get_clean();
        require __DIR__ . '/../../../shared/Views/layout.php';
    }
} // Fin de la clase IngresoController
?>