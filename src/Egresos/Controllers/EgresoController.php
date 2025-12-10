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

        // Soportar prellenado de reembolso desde ingresos
        $prefill = [];
        if (isset($_GET['prellenar_reembolso']) && $_GET['prellenar_reembolso'] == '1' && isset($_GET['from_ingreso'])) {
            $from = (int)$_GET['from_ingreso'];
            // Intentar leer datos del ingreso para prellenar
            require_once __DIR__ . '/../../Ingresos/Models/IngresoModel.php';
            $ingM = new IngresoModel($this->db);
            $ing = $ingM->getIngresoById($from);
            if ($ing) {
                $prefill = [
                    'monto' => $ing['monto'],
                    'destinatario' => $ing['alumno'],
                    'documento_de_amparo' => 'Recibo de ingreso #' . $from,
                    'id_categoria' => 224,
                    'fecha' => date('Y-m-d')
                ];

                // Buscar subpresupuesto hijo del 9999 con id_categoria = 224
                $stmt = $this->db->prepare("SELECT id_presupuesto FROM presupuestos WHERE parent_presupuesto = ? AND id_categoria = ? LIMIT 1");
                if ($stmt) {
                    $parent = 10; $cat = 21;
                    $stmt->bind_param('ii', $parent, $cat);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    $row = $res->fetch_assoc();
                    if ($row) $prefill['id_presupuesto'] = $row['id_presupuesto'];
                    $stmt->close();
                }
            }
        }

        $pageTitle = "Egresos";
        $activeModule = "egresos";

        $this->renderView('egresos_list', [
            'pageTitle' => $pageTitle,
            'activeModule' => $activeModule,
            'egresos' => $egresos,
            'prefill_egreso' => $prefill
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
        // Detectar flujo de reembolso desde el Modal de Reembolsos (robusto)
        $isReembolso = false;
        $folioOrigen = null;
        if (isset($data['reem_folio_origen']) && $data['reem_folio_origen'] !== '') {
            $isReembolso = true;
            $folioOrigen = (int)$data['reem_folio_origen'];
        } elseif (isset($data['from_ingreso']) && $data['from_ingreso'] !== '') {
            $isReembolso = true;
            $folioOrigen = (int)$data['from_ingreso'];
        } else {
            // Heurística: si llegan IDs 11/21, tratar como reembolso
            $cat = isset($data['id_categoria']) ? (int)$data['id_categoria'] : 0;
            $pres = isset($data['id_presupuesto']) ? (int)$data['id_presupuesto'] : 0;
            if ($cat === 21 || $pres === 11) { $isReembolso = true; }
        }
        $isUpdate = !empty($folio_egreso_id);
        $response = ['success' => false];

        // Normalizar monto si viene con formato (ej. 2,000 o $2,000.00)
        if (isset($data['monto'])) {
            $data['monto'] = str_replace(['$',',',' '], '', (string)$data['monto']);
        }
        // Para reembolso, establecer valores por defecto y forzar IDs del sistema
        if ($isReembolso) {
            // Forzar categoría y presupuesto de reembolsos
            $data['id_categoria'] = 21;
            $data['id_presupuesto'] = 11;
            // Establecer proveedor por defecto si falta
            if (empty($data['proveedor'])) { $data['proveedor'] = 'IUM Reembolsos'; }
            // Establecer forma de pago por defecto si falta
            if (empty($data['forma_pago'])) { $data['forma_pago'] = 'Efectivo'; }
        }
        // Debug inicial de entrada
        error_log('[EgresoController.save] isReembolso=' . ($isReembolso ? '1' : '0') . ' isUpdate=' . ($isUpdate ? '1' : '0'));
        error_log('[EgresoController.save] POST keys: ' . implode(',', array_keys($_POST)));

         // --- Validación (igual que antes) ---
         if (empty($data['fecha']) || !isset($data['monto']) || $data['monto'] === '' || empty($data['destinatario']) || empty($data['forma_pago']) || (!$isReembolso && empty($data['proveedor']))) {
             $response['error'] = 'Los campos Fecha, Monto, Categoría, Destinatario y Forma de Pago son obligatorios.';
             echo json_encode($response); exit;
         }
         if (!is_numeric($data['monto']) || $data['monto'] < 0) { $response['error'] = 'El monto no puede ser negativo.'; echo json_encode($response); exit; }
         if (isset($data['activo_fijo']) && !in_array($data['activo_fijo'], ['SI', 'NO'])) { $response['error'] = 'Valor inválido para Activo Fijo.'; echo json_encode($response); exit; }
         $formas_validas = ['Efectivo', 'Transferencia', 'Cheque', 'Tarjeta D.', 'Tarjeta C.'];
         if (!in_array($data['forma_pago'], $formas_validas)) { $response['error'] = 'Forma de pago inválida.'; echo json_encode($response); exit; }
         // Validar categoría sólo si no es reembolso (en reembolso ya se forzó a 21)
         if (!$isReembolso) {
             if (!isset($data['id_categoria']) || !filter_var($data['id_categoria'], FILTER_VALIDATE_INT)) { $response['error'] = 'Categoría inválida.'; echo json_encode($response); exit; }
             $data['id_categoria'] = (int)$data['id_categoria'];
         } else {
             $data['id_categoria'] = 21;
         }
         // --- Fin Validación ---

        try {
            if (!$isUpdate) { // Crear
                if ($isReembolso) {
                    // IDs ya forzados arriba

                    // Verificar que el ingreso origen exista y esté activo (estatus = 1)
                    $stmtChk = $this->db->prepare("SELECT estatus FROM ingresos WHERE folio_ingreso = ? LIMIT 1");
         if (!$isReembolso) {
             if (!isset($data['id_categoria']) || !filter_var($data['id_categoria'], FILTER_VALIDATE_INT)) {
                 $response['error'] = 'Categoría inválida.';
                 $response['debug'] = [
                     'isReembolso' => $isReembolso,
                     'id_categoria' => $data['id_categoria'] ?? null
                 ];
                 echo json_encode($response); exit; 
             }
             $data['id_categoria'] = (int)$data['id_categoria'];
         } else {
             $data['id_categoria'] = 21;
         }
                    $stmtChk->bind_param('i', $folioOrigen);
                    if (!$stmtChk->execute()) {
                        $err = $stmtChk->error ?: 'Desconocido';
                        $stmtChk->close();
                        throw new Exception('Error validando ingreso: ' . $err);
                    }
                    $resChk = $stmtChk->get_result();
                    $rowChk = $resChk->fetch_assoc();
                    $stmtChk->close();
                    if (!$rowChk) { throw new Exception('Ingreso origen no encontrado (folio: ' . $folioOrigen . ').'); }
                    if (intval($rowChk['estatus']) === 0) { throw new Exception('El ingreso ya fue reembolsado previamente.'); }

                    // Transacción: insertar egreso y marcar ingreso como reembolsado
                    $this->db->begin_transaction();
                    $newId = $this->egresoModel->createEgreso($data);
                    if ($newId) {
                        if (!$this->auditoriaModel->hasTriggerForTable('egresos')) {
                            $det = 'Egreso creado (folio: ' . $newId . ')';
                            $this->auditoriaModel->addLog('Egreso', 'Insercion', $det, null, json_encode($data), $newId, null, $_SESSION['user_id'] ?? null);
                        }

                        $stmtU = $this->db->prepare("UPDATE ingresos SET estatus = 0 WHERE folio_ingreso = ?");
                        if (!$stmtU) { throw new Exception('Error preparando actualización de ingreso: ' . $this->db->error); }
                        $stmtU->bind_param('i', $folioOrigen);
                        if (!$stmtU->execute()) {
                            $err = $stmtU->error ?: 'Desconocido';
                            $stmtU->close();
                            throw new Exception('Error actualizando ingreso: ' . $err);
                        }
                        if ($stmtU->affected_rows === 0) {
                            $stmtU->close();
                            throw new Exception('Ingreso no encontrado o ya reembolsado (folio: ' . $folioOrigen . ').');
                        }
                        $stmtU->close();

                        $this->db->commit();
                        $response['success'] = true;
                        $response['folio'] = $newId;
                    } else {
                        $this->db->rollback();
                        $response['error'] = 'No se pudo crear el egreso.';
                    }
                } else {
                    // Egreso normal: validar presupuesto y límites
                    $selectedPres = isset($data['id_presupuesto']) ? (int)$data['id_presupuesto'] : 0;
                    if ($selectedPres <= 0) { $response['error'] = 'Presupuesto inválido.'; echo json_encode($response); exit; }

                    $presObj = $this->presupuestoModel->getPresupuestoById($selectedPres);
                    if (!$presObj) { $response['error'] = 'Presupuesto no encontrado.'; echo json_encode($response); exit; }

                    $montoNuevo = floatval($data['monto']);
                    $isPermanent = isset($presObj['es_permanente']) && intval($presObj['es_permanente']) === 1;
                    if (!$isPermanent) {
                        $gastadoPres = $this->presupuestoModel->getGastadoEnPresupuesto($selectedPres);
                        if (($gastadoPres + $montoNuevo) > floatval($presObj['monto_limite'])) {
                            $response['error'] = 'El monto excede el límite del presupuesto de la categoría.';
                            echo json_encode($response); exit;
                        }
                        $parentId = isset($presObj['parent_presupuesto']) ? (int)$presObj['parent_presupuesto'] : 0;
                        if ($parentId > 0) {
                            $gastadoGeneral = $this->presupuestoModel->getGastadoEnPresupuesto($parentId);
                            $parentObj = $this->presupuestoModel->getPresupuestoById($parentId);
                            $parentLim = floatval($parentObj['monto_limite'] ?? 0);
                            if (($gastadoGeneral + $montoNuevo) > $parentLim) {
                                $response['error'] = 'El monto excede el límite del Presupuesto General asociado.';
                                echo json_encode($response); exit;
                            }
                        }
                    }

                    // Solo insertar el egreso
                    $newId = $this->egresoModel->createEgreso($data);
                    if ($newId) {
                        if (!$this->auditoriaModel->hasTriggerForTable('egresos')) {
                            $det = 'Egreso creado (folio: ' . $newId . ')';
                            $this->auditoriaModel->addLog('Egreso', 'Insercion', $det, null, json_encode($data), $newId, null, $_SESSION['user_id'] ?? null);
                        }
                        $response['success'] = true;
                        $response['folio'] = $newId;
                    } else {
                        $response['error'] = 'No se pudo crear el egreso.';
                    }
                }
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
            // Revertir cualquier transacción abierta
            try { if ($this->db) $this->db->rollback(); } catch (Exception $ex) { }
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
     * Acción AJAX: Actualiza ÚNICAMENTE el monto de un egreso.
     */
    public function updateMonto() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['success' => false, 'error' => 'No autorizado']); exit; }

        $folio_egreso_id = $_POST['id'] ?? 0;
        $nuevoMonto = $_POST['monto'] ?? null;
        $response = ['success' => false];

        if ($folio_egreso_id <= 0) {
            $response['error'] = 'ID de egreso inválido.';
            echo json_encode($response); exit;
        }

        if ($nuevoMonto === null || $nuevoMonto === '' || !is_numeric($nuevoMonto) || $nuevoMonto < 0) {
            $response['error'] = 'El monto no puede ser negativo.';
            echo json_encode($response); exit;
        }

        try {
            $oldData = $this->egresoModel->getEgresoById($folio_egreso_id);
            if (!$oldData) {
                $response['error'] = 'Egreso no encontrado.';
                echo json_encode($response); exit;
            }

            // Armar el arreglo completo que espera updateEgreso, reutilizando los datos actuales
            $dataUpdate = [
                'proveedor'            => $oldData['proveedor'] ?? null,
                'descripcion'          => $oldData['descripcion'] ?? null,
                'monto'                => $nuevoMonto,
                'fecha'                => $oldData['fecha'] ?? null,
                'destinatario'         => $oldData['destinatario'] ?? null,
                'forma_pago'           => $oldData['forma_pago'] ?? null,
                'documento_de_amparo'  => $oldData['documento_de_amparo'] ?? null,
                'id_user'              => $_SESSION['user_id'],
                'id_categoria'         => $oldData['id_categoria'] ?? null,
            ];

            $success = $this->egresoModel->updateEgreso($folio_egreso_id, $dataUpdate);

            if ($success) {
                if (!$this->auditoriaModel->hasTriggerForTable('egresos')) {
                    $newData = $oldData;
                    $newData['monto'] = $nuevoMonto;
                    $this->auditoriaModel->addLog(
                        'Egreso',
                        'ActualizacionMonto',
                        'Actualización de monto de egreso',
                        json_encode($oldData),
                        json_encode($newData),
                        $folio_egreso_id,
                        null,
                        $_SESSION['user_id'] ?? null
                    );
                }
                $response['success'] = true;
            } else {
                $response['error'] = 'No se pudo actualizar el monto del egreso.';
            }
        } catch (Exception $e) {
            error_log("Error en EgresoController->updateMonto: " . $e->getMessage());
            $response['error'] = $e->getMessage() ?: 'Error interno del servidor al actualizar el monto.';
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