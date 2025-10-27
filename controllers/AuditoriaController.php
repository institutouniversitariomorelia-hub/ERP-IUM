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
        ];

        // Pedir datos a los Modelos
        $auditoriaLogs = $this->auditoriaModel->getAuditoriaLogs($filtros);
        $usuarios = $this->userModel->getAllUsers(); // Obtener lista de usuarios para el <select>

        // Datos para la Vista
        $pageTitle = "Historial de Auditoría";
        $activeModule = "auditoria";

        // Renderizar la Vista dentro del Layout
        $this->renderView('auditoria_list', [
            'pageTitle' => $pageTitle,
            'activeModule' => $activeModule,
            'auditoriaLogs' => $auditoriaLogs,
            'usuarios' => $usuarios, // Pasar usuarios al <select>
            'filtrosActuales' => $filtros // Pasar filtros actuales para mantenerlos en el form
        ]);
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