<?php
// src/Auditoria/Controllers/AuditoriaController.php

require_once __DIR__ . '/../Models/AuditoriaModel.php';
require_once __DIR__ . '/../../Auth/Models/UserModel.php';

class AuditoriaController {
    private $db;
    private $auditoriaModel;
    private $userModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->auditoriaModel = new AuditoriaModel($dbConnection);
        $this->userModel = new UserModel($dbConnection); // Instanciar UserModel
    }

    /**
     * Acción principal: Muestra la página de auditoría con filtros y tablas.
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login'); exit; }

        // Recoger filtros de la URL (si se enviaron por GET desde el formulario)
        $filtros = [
            'seccion'      => $_GET['seccion'] ?? null,
            'usuario'      => $_GET['usuario'] ?? null,
            'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
            'fecha_fin'    => $_GET['fecha_fin'] ?? null,
            'accion_tipo'  => $_GET['accion_tipo'] ?? ($_GET['tipo_accion'] ?? null),
            // Nota: filtros 'accion' y 'q' eliminados para simplificar la interfaz y evitar búsquedas difusas.
        ];

        // Mapear nombres legibles de la UI a los valores reales en la BD (evita mismatch por mayúsculas/plurales)
        $seccionMap = [
            'Usuario' => 'usuarios',
            'Egreso' => 'egresos',
            'Ingreso' => 'ingresos',
            'Categoria' => 'categorias',
            'Presupuesto' => 'presupuestos'
        ];
        if (!empty($filtros['seccion']) && isset($seccionMap[$filtros['seccion']])) {
            $filtros['seccion'] = $seccionMap[$filtros['seccion']];
        }

        // Mantener 'accion_tipo' tal cual; el filtrado específico se realiza en el modelo.

        // Soporte de paginación: respetar siempre la página enviada por GET.
        // El formulario de filtros ya envía page=1 en un input hidden,
        // y los enlaces de paginación construyen el page correcto.
        $filtros['page'] = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $filtros['pageSize'] = isset($_GET['pageSize']) ? max(1, min(200, (int)$_GET['pageSize'])) : 10;

        $logsResult = $this->auditoriaModel->getAuditoriaLogs($filtros);
        if (is_array($logsResult) && array_key_exists('data', $logsResult)) {
            $auditoriaLogs = $logsResult['data'];
            $totalLogs = $logsResult['total'] ?? count($auditoriaLogs);
            $page = $logsResult['page'] ?? $filtros['page'];
            $pageSize = $logsResult['pageSize'] ?? $filtros['pageSize'];
        } else {
            // Compatibilidad: si el modelo devolviera solo un array de filas
            $auditoriaLogs = is_array($logsResult) ? $logsResult : [];
            $totalLogs = count($auditoriaLogs);
            $page = $filtros['page'];
            $pageSize = $filtros['pageSize'];
        }

        // Para auditoría necesitamos ver TODOS los usuarios, incluyendo SU
        $usuarios = $this->userModel->getAllUsersForAudit(); // Obtener lista de usuarios para el <select>
        // Últimos movimientos (compacto)
        $recentLogs = $this->auditoriaModel->getRecentLogs(5);

        // Datos para la Vista
        $pageTitle = "Historial de Auditoría";
        $activeModule = "auditoria";

        // Renderizar la Vista dentro del Layout
        $this->renderView('auditoria_list', [
            'pageTitle' => $pageTitle,
            'activeModule' => $activeModule,
        'auditoriaLogs' => $auditoriaLogs,
            'usuarios' => $usuarios, // Pasar usuarios al <select>
            'filtrosActuales' => $filtros, // Pasar filtros actuales para mantenerlos en el form
            'recentLogs' => $recentLogs
        ,'totalLogs' => $totalLogs
        ,'page' => $page
        ,'pageSize' => $pageSize
        ]);
    }

    /**
     * Acción AJAX: Devuelve un registro de auditoría por id en JSON (para el modal).
     */
    public function getLogAjax() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }
        $id = $_GET['id'] ?? 0;
        $id = (int)$id;
        if ($id <= 0) { echo json_encode(['error' => 'ID inválido']); exit; }
        $log = $this->auditoriaModel->getAuditoriaById($id);
        if ($log) echo json_encode(['success' => true, 'data' => $log]);
        else echo json_encode(['success' => false, 'error' => 'Registro no encontrado.']);
        exit;
    }

    /**
     * Acción AJAX: Devuelve detalles completos de un registro de auditoría (para el modal de detalles).
     */
    public function getDetalle() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) { 
            echo json_encode(['success' => false, 'message' => 'No autorizado']); 
            exit; 
        }
        
        $id = $_GET['id'] ?? 0;
        $id = (int)$id;
        
        if ($id <= 0) { 
            echo json_encode(['success' => false, 'message' => 'ID inválido']); 
            exit; 
        }
        
        $log = $this->auditoriaModel->getAuditoriaById($id);
        
        if ($log) {
            // Enriquecer con información del usuario si está disponible en los valores JSON
            $usuarioNombre = 'Sistema';
            
            if (!empty($log['new_valor'])) {
                $jsonData = json_decode($log['new_valor'], true);
                if (isset($jsonData['nombre'])) {
                    $usuarioNombre = $jsonData['nombre'];
                } elseif (isset($jsonData['username'])) {
                    $usuarioNombre = $jsonData['username'];
                }
            }
            
            $log['usuario'] = $usuarioNombre;
            
            echo json_encode(['success' => true, 'data' => $log]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Registro no encontrado']);
        }
        exit;
    }

    /**
     * Generar reporte de auditoría
     */
    public function generarReporte() {
        header('Content-Type: application/json');
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'No autorizado']);
            exit;
        }

        $tipo = $_GET['tipo'] ?? 'personalizado';
        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;

        try {
            // Calcular fechas según el tipo
            if ($tipo === 'semanal') {
                $fechaFin = date('Y-m-d');
                $fechaInicio = date('Y-m-d', strtotime('-7 days'));
            } elseif ($tipo === 'mensual') {
                $fechaFin = date('Y-m-d');
                $fechaInicio = date('Y-m-01'); // Primer día del mes actual
            }

            if (!$fechaInicio || !$fechaFin) {
                echo json_encode(['success' => false, 'error' => 'Fechas no válidas']);
                exit;
            }

            // Obtener logs del rango - Usar la estructura correcta de la tabla auditoria
            $sql = "SELECT a.id_auditoria,
                           a.fecha_hora,
                           DATE(a.fecha_hora) as fecha,
                           TIME(a.fecha_hora) as hora,
                           a.seccion,
                           a.accion,
                           a.old_valor,
                           a.new_valor,
                           a.folio_egreso,
                           a.folio_ingreso
                    FROM auditoria a 
                    WHERE DATE(a.fecha_hora) BETWEEN ? AND ? 
                    ORDER BY a.fecha_hora DESC";
            
            $stmt = $this->db->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error en preparación SQL: " . $this->db->error);
            }
            
            $stmt->bind_param("ss", $fechaInicio, $fechaFin);
            $stmt->execute();
            $result = $stmt->get_result();
            $logs = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Calcular estadísticas
            $totalLogs = count($logs);
            $porSeccion = [];
            $porAccion = [];
            $porUsuario = [];
            
            // Formatear logs y calcular estadísticas
            $movimientos = [];
            foreach ($logs as $log) {
                // Extraer usuario del JSON (si está disponible en old_valor o new_valor)
                $usuarioNombre = 'Sistema';
                $usuarioUsername = '';
                
                if (!empty($log['new_valor'])) {
                    $jsonData = json_decode($log['new_valor'], true);
                    if (isset($jsonData['nombre'])) {
                        $usuarioNombre = $jsonData['nombre'];
                    }
                    if (isset($jsonData['username'])) {
                        $usuarioUsername = $jsonData['username'];
                    }
                }
                
                // Por sección
                $seccion = $log['seccion'] ?? 'Sin especificar';
                if (!isset($porSeccion[$seccion])) {
                    $porSeccion[$seccion] = 0;
                }
                $porSeccion[$seccion]++;

                // Por acción
                $accion = $log['accion'] ?? 'Sin especificar';
                if (!isset($porAccion[$accion])) {
                    $porAccion[$accion] = 0;
                }
                $porAccion[$accion]++;

                // Por usuario (usar el extraído del JSON o 'Sistema')
                if (!isset($porUsuario[$usuarioNombre])) {
                    $porUsuario[$usuarioNombre] = 0;
                }
                $porUsuario[$usuarioNombre]++;

                // Formatear detalles para mostrar en la tabla
                $detalles = '';
                if (!empty($log['old_valor']) && !empty($log['new_valor'])) {
                    $detalles = 'Cambio de valores';
                } elseif (!empty($log['new_valor'])) {
                    $detalles = 'Nuevo registro';
                } elseif (!empty($log['old_valor'])) {
                    $detalles = 'Registro eliminado';
                }
                
                // Crear objeto formateado para el frontend
                $movimientos[] = [
                    'id_auditoria' => $log['id_auditoria'],
                    'fecha_hora' => $log['fecha_hora'],
                    'fecha' => $log['fecha'],
                    'hora' => $log['hora'],
                    'usuario_nombre' => $usuarioNombre,
                    'usuario_username' => $usuarioUsername,
                    'seccion' => $seccion,
                    'accion' => $accion,
                    'detalles' => $detalles,
                    'old_valor' => $log['old_valor'] ?? '',
                    'new_valor' => $log['new_valor'] ?? ''
                ];
            }

            echo json_encode([
                'success' => true,
                'tipo' => $tipo,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'movimientos' => $movimientos,
                'totalLogs' => $totalLogs,
                'porSeccion' => $porSeccion,
                'porAccion' => $porAccion,
                'porUsuario' => $porUsuario
            ]);

        } catch (Exception $e) {
            error_log("Error en generarReporte: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error al generar reporte: ' . $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Acción AJAX: Obtiene los logs de auditoría (usada por el JS si no recargamos página).
     * Nota: Actualmente recargamos la página al filtrar, así que esta acción AJAX no se usa,
     * pero la dejamos como ejemplo si quisiéramos actualizar la tabla dinámicamente.
     */
     /*
     public function getLogsAjax() {
         header('Content-Type: application/json');
         if (!isset($_SESSION['user_id'])) { echo json_encode(['error' => 'No autorizado']); exit; }
         
         $filtros = $_GET; // Recoger filtros de la petición GET (o POST)
         $logs = $this->auditoriaModel->getAuditoriaLogs($filtros);
         echo json_encode($logs);
         exit;
     }
     */

     /**
     * Función helper para renderizar vistas.
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
}
?>