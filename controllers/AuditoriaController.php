<?php
// controllers/AuditoriaController.php

require_once 'models/AuditoriaModel.php';
require_once 'models/UserModel.php'; // Necesitamos la lista de usuarios para el filtro

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
            'seccion' => $_GET['seccion'] ?? null,
            'usuario' => $_GET['usuario'] ?? null,
            'fecha_inicio' => $_GET['fecha_inicio'] ?? null,
            'fecha_fin' => $_GET['fecha_fin'] ?? null,
            'accion' => $_GET['accion'] ?? null,
            // soporte para filtro por tipo (accion_tipo) desde la UI: Registro/Actualizacion/Eliminacion
            'accion_tipo' => $_GET['accion_tipo'] ?? null,
            'q' => $_GET['q'] ?? null,
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

        // Si se indicó accion_tipo, priorizarlo y normalizar su valor hacia la clave 'accion'
        if (!empty($filtros['accion_tipo'])) {
            // Valores esperados: Insercion, Actualizacion, Eliminacion
            $filtros['accion'] = $filtros['accion_tipo'];
        }

    // Pedir datos a los Modelos
    // Soporte de paginación: leer página y tamaño desde GET
    $filtros['page'] = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $filtros['pageSize'] = isset($_GET['pageSize']) ? max(1, min(200, (int)$_GET['pageSize'])) : 50;

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

    $usuarios = $this->userModel->getAllUsers(); // Obtener lista de usuarios para el <select>
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
        require "views/{$view}.php";
        $content = ob_get_clean(); 
        require 'views/layout.php'; 
    }
}
?>