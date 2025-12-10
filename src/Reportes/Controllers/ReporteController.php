<?php
// src/Reportes/Controllers/ReporteController.php

require_once __DIR__ . '/../../Ingresos/Models/IngresoModel.php';
require_once __DIR__ . '/../../Egresos/Models/EgresoModel.php';
require_once __DIR__ . '/../../Categorias/Models/CategoriaModel.php';

class ReporteController {
    private $db;
    private $ingresoModel;
    private $egresoModel;
    private $categoriaModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->ingresoModel = new IngresoModel($dbConnection);
        $this->egresoModel = new EgresoModel($dbConnection);
        $this->categoriaModel = new CategoriaModel($dbConnection);
    }

    /**
     * Vista principal de reportes
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login');
            exit;
        }

        $pageTitle = "Generación de Reportes";
        $activeModule = "reportes";
        
        $currentUser = [
            'id' => $_SESSION['user_id'],
            'nombre' => $_SESSION['user_nombre'],
            'username' => $_SESSION['user_username'],
            'rol' => $_SESSION['user_rol']
        ];

        $this->renderView('reportes', [
            'pageTitle' => $pageTitle,
            'activeModule' => $activeModule,
            'currentUser' => $currentUser
        ]);
    }

    /**
     * Generar reporte de ingresos
     */
    public function generarIngresos() {
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

            // Obtener ingresos del rango
                $sql = "SELECT i.*, c.nombre as nombre_categoria 
                    FROM ingresos i 
                    LEFT JOIN categorias c ON i.id_categoria = c.id_categoria 
                    WHERE i.fecha BETWEEN ? AND ? 
                      AND COALESCE(i.estatus, 1) = 1
                    ORDER BY i.fecha DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ss", $fechaInicio, $fechaFin);
            $stmt->execute();
            $result = $stmt->get_result();
            $ingresos = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Calcular totales
            $total = 0;
            $porCategoria = [];
            
            foreach ($ingresos as $ingreso) {
                $monto = floatval($ingreso['monto']);
                $total += $monto;
                
                $categoria = $ingreso['nombre_categoria'] ?? 'Sin categoría';
                if (!isset($porCategoria[$categoria])) {
                    $porCategoria[$categoria] = 0;
                }
                $porCategoria[$categoria] += $monto;
            }

            echo json_encode([
                'success' => true,
                'tipo' => $tipo,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'ingresos' => $ingresos,
                'total' => $total,
                'porCategoria' => $porCategoria,
                'cantidad' => count($ingresos)
            ]);

        } catch (Exception $e) {
            error_log("Error en generarIngresos: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error al generar reporte']);
        }
        exit;
    }

    /**
     * Generar reporte de egresos
     */
    public function generarEgresos() {
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
                $fechaInicio = date('Y-m-01');
            }

            if (!$fechaInicio || !$fechaFin) {
                echo json_encode(['success' => false, 'error' => 'Fechas no válidas']);
                exit;
            }

            // Obtener egresos del rango
            $sql = "SELECT e.*, c.nombre as nombre_categoria 
                    FROM egresos e 
                    LEFT JOIN categorias c ON e.id_categoria = c.id_categoria 
                    WHERE e.fecha BETWEEN ? AND ? 
                    ORDER BY e.fecha DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bind_param("ss", $fechaInicio, $fechaFin);
            $stmt->execute();
            $result = $stmt->get_result();
            $egresos = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            // Calcular totales
            $total = 0;
            $porCategoria = [];
            
            foreach ($egresos as $egreso) {
                $monto = floatval($egreso['monto']);
                $total += $monto;
                
                $categoria = $egreso['nombre_categoria'] ?? 'Sin categoría';
                if (!isset($porCategoria[$categoria])) {
                    $porCategoria[$categoria] = 0;
                }
                $porCategoria[$categoria] += $monto;
            }

            echo json_encode([
                'success' => true,
                'tipo' => $tipo,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'egresos' => $egresos,
                'total' => $total,
                'porCategoria' => $porCategoria,
                'cantidad' => count($egresos)
            ]);

        } catch (Exception $e) {
            error_log("Error en generarEgresos: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error al generar reporte']);
        }
        exit;
    }

    /**
     * Generar reporte consolidado (ingresos + egresos)
     */
    public function generarConsolidado() {
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
                $fechaInicio = date('Y-m-01');
            }

            if (!$fechaInicio || !$fechaFin) {
                echo json_encode(['success' => false, 'error' => 'Fechas no válidas']);
                exit;
            }

            // Obtener totales de ingresos
            $sqlIngresos = "SELECT SUM(monto) as total FROM ingresos WHERE fecha BETWEEN ? AND ? AND COALESCE(estatus, 1) = 1";
            $stmt = $this->db->prepare($sqlIngresos);
            $stmt->bind_param("ss", $fechaInicio, $fechaFin);
            $stmt->execute();
            $totalIngresos = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
            $stmt->close();

            // Obtener totales de egresos
            $sqlEgresos = "SELECT SUM(monto) as total FROM egresos WHERE fecha BETWEEN ? AND ?";
            $stmt = $this->db->prepare($sqlEgresos);
            $stmt->bind_param("ss", $fechaInicio, $fechaFin);
            $stmt->execute();
            $totalEgresos = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
            $stmt->close();

            $balance = floatval($totalIngresos) - floatval($totalEgresos);

            echo json_encode([
                'success' => true,
                'tipo' => $tipo,
                'fechaInicio' => $fechaInicio,
                'fechaFin' => $fechaFin,
                'totalIngresos' => floatval($totalIngresos),
                'totalEgresos' => floatval($totalEgresos),
                'balance' => $balance
            ]);

        } catch (Exception $e) {
            error_log("Error en generarConsolidado: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error al generar reporte']);
        }
        exit;
    }

    /**
     * Función helper para renderizar vistas
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
