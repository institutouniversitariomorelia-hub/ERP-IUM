<?php
// controllers/DashboardController.php

require_once __DIR__ . '/../models/IngresoModel.php';
require_once __DIR__ . '/../models/EgresoModel.php';
require_once __DIR__ . '/../models/PresupuestoModel.php';
require_once __DIR__ . '/../models/CategoriaModel.php';

class DashboardController {
    private $db;
    private $ingresoModel;
    private $egresoModel;
    private $presupuestoModel;
    private $categoriaModel;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
        $this->ingresoModel = new IngresoModel($dbConnection);
        $this->egresoModel = new EgresoModel($dbConnection);
        $this->presupuestoModel = new PresupuestoModel($dbConnection);
        $this->categoriaModel = new CategoriaModel($dbConnection);
    }

    /**
     * Vista principal del dashboard
     */
    public function index() {
        $pageTitle = 'Dashboard Ejecutivo';
        $activeModule = 'dashboard';
        
        // Cargar el layout que incluirá la vista dashboard.php
        ob_start();
        require_once __DIR__ . '/../views/dashboard.php';
        $content = ob_get_clean();
        require_once __DIR__ . '/../views/layout.php';
    }

    /**
     * Obtiene datos para el resumen mensual (tarjetas principales)
     * Retorna JSON con totales de ingresos y egresos del mes actual
     */
    public function getResumenMensual() {
        header('Content-Type: application/json');
        
        try {
            $mesActual = date('Y-m');
            
            // Total de ingresos del mes actual
            $queryIngresos = "SELECT COALESCE(SUM(monto), 0) as total 
                             FROM ingresos 
                             WHERE DATE_FORMAT(fecha, '%Y-%m') = ?";
            $stmtI = $this->db->prepare($queryIngresos);
            $stmtI->bind_param('s', $mesActual);
            $stmtI->execute();
            $totalIngresos = $stmtI->get_result()->fetch_assoc()['total'];
            $stmtI->close();
            
            // Total de egresos del mes actual
            $queryEgresos = "SELECT COALESCE(SUM(monto), 0) as total 
                            FROM egresos 
                            WHERE DATE_FORMAT(fecha, '%Y-%m') = ?";
            $stmtE = $this->db->prepare($queryEgresos);
            $stmtE->bind_param('s', $mesActual);
            $stmtE->execute();
            $totalEgresos = $stmtE->get_result()->fetch_assoc()['total'];
            $stmtE->close();
            
            $balance = $totalIngresos - $totalEgresos;
            
            echo json_encode([
                'success' => true,
                'ingresos' => floatval($totalIngresos),
                'egresos' => floatval($totalEgresos),
                'balance' => floatval($balance),
                'mes' => date('F Y')
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene datos para gráfica de ingresos vs egresos por mes
     * Retorna JSON con arrays de meses, ingresos y egresos
     * Acepta parámetros: 'meses' (rango), o 'mes' + 'anio' (específico)
     */
    public function getIngresosEgresosPorMes() {
        header('Content-Type: application/json');
        
        try {
            $meses = [];
            $ingresos = [];
            $egresos = [];
            
            // Verificar si se solicita un mes específico
            $mesEspecifico = isset($_GET['mes']) ? intval($_GET['mes']) : null;
            $anioEspecifico = isset($_GET['anio']) ? intval($_GET['anio']) : null;
            
            if ($mesEspecifico && $anioEspecifico) {
                // Búsqueda específica por mes/año
                $mes = sprintf('%04d-%02d', $anioEspecifico, $mesEspecifico);
                $nombreMes = date('M Y', strtotime("$anioEspecifico-$mesEspecifico-01"));
                
                // Ingresos del mes
                $queryI = "SELECT COALESCE(SUM(monto), 0) as total 
                          FROM ingresos 
                          WHERE DATE_FORMAT(fecha, '%Y-%m') = ?";
                $stmtI = $this->db->prepare($queryI);
                $stmtI->bind_param('s', $mes);
                $stmtI->execute();
                $totalI = $stmtI->get_result()->fetch_assoc()['total'];
                $stmtI->close();
                
                // Egresos del mes
                $queryE = "SELECT COALESCE(SUM(monto), 0) as total 
                          FROM egresos 
                          WHERE DATE_FORMAT(fecha, '%Y-%m') = ?";
                $stmtE = $this->db->prepare($queryE);
                $stmtE->bind_param('s', $mes);
                $stmtE->execute();
                $totalE = $stmtE->get_result()->fetch_assoc()['total'];
                $stmtE->close();
                
                $meses[] = $nombreMes;
                $ingresos[] = floatval($totalI);
                $egresos[] = floatval($totalE);
                
            } else {
                // Búsqueda por rango de meses
                $numMeses = isset($_GET['meses']) ? intval($_GET['meses']) : 6;
                
                // Validar que sea un número razonable
                if ($numMeses < 1) $numMeses = 1;
                if ($numMeses > 12) $numMeses = 12;
                
                // Obtener los últimos N meses
                for ($i = $numMeses - 1; $i >= 0; $i--) {
                    $mes = date('Y-m', strtotime("-$i months"));
                    $nombreMes = date('M Y', strtotime("-$i months"));
                    
                    // Ingresos del mes
                    $queryI = "SELECT COALESCE(SUM(monto), 0) as total 
                              FROM ingresos 
                              WHERE DATE_FORMAT(fecha, '%Y-%m') = ?";
                    $stmtI = $this->db->prepare($queryI);
                    $stmtI->bind_param('s', $mes);
                    $stmtI->execute();
                    $totalI = $stmtI->get_result()->fetch_assoc()['total'];
                    $stmtI->close();
                    
                    // Egresos del mes
                    $queryE = "SELECT COALESCE(SUM(monto), 0) as total 
                              FROM egresos 
                              WHERE DATE_FORMAT(fecha, '%Y-%m') = ?";
                    $stmtE = $this->db->prepare($queryE);
                    $stmtE->bind_param('s', $mes);
                    $stmtE->execute();
                    $totalE = $stmtE->get_result()->fetch_assoc()['total'];
                    $stmtE->close();
                    
                    $meses[] = $nombreMes;
                    $ingresos[] = floatval($totalI);
                    $egresos[] = floatval($totalE);
                }
            }
            
            echo json_encode([
                'success' => true,
                'meses' => $meses,
                'ingresos' => $ingresos,
                'egresos' => $egresos
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene distribución de ingresos por categoría
     * Retorna JSON con categorías y montos
     */
    public function getIngresosPorCategoria() {
        header('Content-Type: application/json');
        
        try {
            $query = "SELECT c.nombre, COALESCE(SUM(i.monto), 0) as total
                     FROM categorias c
                     LEFT JOIN ingresos i ON i.id_categoria = c.id_categoria
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
    }

    /**
     * Obtiene distribución de egresos por categoría
     * Retorna JSON con categorías y montos
     */
    public function getEgresosPorCategoria() {
        header('Content-Type: application/json');
        
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
    }

    /**
     * Obtiene alertas de presupuestos (categorías con >90% consumido)
     * Retorna JSON con array de alertas
     */
    public function getAlertasPresupuesto() {
        header('Content-Type: application/json');
        
        try {
            // Obtener presupuestos y calcular el gasto total por categoría (sin filtrar por mes)
            $query = "SELECT 
                        c.nombre as categoria,
                        p.monto_limite as presupuesto,
                        COALESCE(
                            (SELECT SUM(e.monto) 
                             FROM egresos e 
                             WHERE e.id_categoria = p.id_categoria), 
                            0
                        ) as gastado
                     FROM presupuestos p
                     INNER JOIN categorias c ON c.id_categoria = p.id_categoria
                     WHERE p.monto_limite > 0
                     ORDER BY p.id_presupuesto DESC";
            
            $result = $this->db->query($query);
            $alertas = [];
            
            while ($row = $result->fetch_assoc()) {
                $presupuesto = floatval($row['presupuesto']);
                $gastado = floatval($row['gastado']);
                
                if ($presupuesto > 0) {
                    $porcentaje = round(($gastado / $presupuesto) * 100, 2);
                    
                    // Solo incluir si el porcentaje es >= 70%
                    if ($porcentaje >= 70) {
                        $alertas[] = [
                            'categoria' => $row['categoria'],
                            'presupuesto' => $presupuesto,
                            'gastado' => $gastado,
                            'porcentaje' => $porcentaje
                        ];
                    }
                }
            }
            
            // Ordenar por porcentaje descendente
            usort($alertas, function($a, $b) {
                return $b['porcentaje'] <=> $a['porcentaje'];
            });
            
            echo json_encode([
                'success' => true,
                'alertas' => $alertas
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Obtiene balance mensual (ingresos - egresos) para gráfica de tendencia
     * Retorna JSON con meses y balances de los últimos 6 meses
     */
    public function getTendenciaBalance() {
        header('Content-Type: application/json');
        
        try {
            $meses = [];
            $balances = [];
            
            for ($i = 5; $i >= 0; $i--) {
                $mes = date('Y-m', strtotime("-$i months"));
                $nombreMes = date('M Y', strtotime("-$i months"));
                
                // Ingresos del mes
                $queryI = "SELECT COALESCE(SUM(monto), 0) as total FROM ingresos WHERE DATE_FORMAT(fecha, '%Y-%m') = ?";
                $stmtI = $this->db->prepare($queryI);
                $stmtI->bind_param('s', $mes);
                $stmtI->execute();
                $totalI = $stmtI->get_result()->fetch_assoc()['total'];
                $stmtI->close();
                
                // Egresos del mes
                $queryE = "SELECT COALESCE(SUM(monto), 0) as total FROM egresos WHERE DATE_FORMAT(fecha, '%Y-%m') = ?";
                $stmtE = $this->db->prepare($queryE);
                $stmtE->bind_param('s', $mes);
                $stmtE->execute();
                $totalE = $stmtE->get_result()->fetch_assoc()['total'];
                $stmtE->close();
                
                $meses[] = $nombreMes;
                $balances[] = floatval($totalI - $totalE);
            }
            
            echo json_encode([
                'success' => true,
                'meses' => $meses,
                'balances' => $balances
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
?>
