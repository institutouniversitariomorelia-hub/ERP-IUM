<?php
// generate_comparativa_dashboard.php
// Generador de reporte imprimible de comparativa Ingresos vs Egresos desde Dashboard

// Iniciar sesión solo si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir BASE_URL si no está definida
if (!defined('BASE_URL')) {
    define('BASE_URL', '/erp-ium/');
}

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/shared/Helpers/helpers.php';

// Verificar sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login');
    exit;
}

// Obtener parámetros
$formato = isset($_GET['formato']) ? $_GET['formato'] : 'html';
$meses = isset($_GET['meses']) ? intval($_GET['meses']) : 6;
$mesEspecifico = isset($_GET['mes']) ? intval($_GET['mes']) : null;
$anioEspecifico = isset($_GET['anio']) ? intval($_GET['anio']) : null;

// Preparar datos
$datosComparativa = [];
$tituloReporte = '';

if ($mesEspecifico && $anioEspecifico) {
    // Búsqueda específica por mes/año
    $mes = sprintf('%04d-%02d', $anioEspecifico, $mesEspecifico);
    $fecha = DateTime::createFromFormat('Y-m-d', "$anioEspecifico-$mesEspecifico-01");
    function nombreMesEsp($fecha) {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
            7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        $mes = (int)$fecha->format('n');
        $anio = $fecha->format('Y');
        return ucfirst($meses[$mes]) . ' ' . $anio;
    }
    $nombreMes = nombreMesEsp($fecha);
    $tituloReporte = "Comparativa de $nombreMes";
    
    // Ingresos del mes
    $queryI = "SELECT COALESCE(SUM(monto), 0) as total FROM ingresos WHERE DATE_FORMAT(fecha, '%Y-%m') = ?";
    $stmtI = $conn->prepare($queryI);
    $stmtI->bind_param('s', $mes);
    $stmtI->execute();
    $totalI = $stmtI->get_result()->fetch_assoc()['total'];
    $stmtI->close();
    
    // Egresos del mes
    $queryE = "SELECT COALESCE(SUM(monto), 0) as total FROM egresos WHERE DATE_FORMAT(fecha, '%Y-%m') = ?";
    $stmtE = $conn->prepare($queryE);
    $stmtE->bind_param('s', $mes);
    $stmtE->execute();
    $totalE = $stmtE->get_result()->fetch_assoc()['total'];
    $stmtE->close();
    
    $datosComparativa[] = [
        'mes' => $nombreMes,
        'ingresos' => floatval($totalI),
        'egresos' => floatval($totalE),
        'balance' => floatval($totalI - $totalE)
    ];
    
} else {
    // Búsqueda por rango de meses
    $tituloReporte = $meses === 1 ? "Comparativa del Último Mes" : "Comparativa de los Últimos $meses Meses";
    
    function nombreMesEsp($fecha) {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril', 5 => 'mayo', 6 => 'junio',
            7 => 'julio', 8 => 'agosto', 9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        $mes = (int)$fecha->format('n');
        $anio = $fecha->format('Y');
        return ucfirst($meses[$mes]) . ' ' . $anio;
    }
    for ($i = $meses - 1; $i >= 0; $i--) {
        $mes = date('Y-m', strtotime("-$i months"));
        $fecha = new DateTime("-$i months");
        $nombreMes = nombreMesEsp($fecha);
        
        // Ingresos del mes
        $queryI = "SELECT COALESCE(SUM(monto), 0) as total FROM ingresos WHERE DATE_FORMAT(fecha, '%Y-%m') = ?";
        $stmtI = $conn->prepare($queryI);
        $stmtI->bind_param('s', $mes);
        $stmtI->execute();
        $totalI = $stmtI->get_result()->fetch_assoc()['total'];
        $stmtI->close();
        
        // Egresos del mes
        $queryE = "SELECT COALESCE(SUM(monto), 0) as total FROM egresos WHERE DATE_FORMAT(fecha, '%Y-%m') = ?";
        $stmtE = $conn->prepare($queryE);
        $stmtE->bind_param('s', $mes);
        $stmtE->execute();
        $totalE = $stmtE->get_result()->fetch_assoc()['total'];
        $stmtE->close();
        
        $datosComparativa[] = [
            'mes' => $nombreMes,
            'ingresos' => floatval($totalI),
            'egresos' => floatval($totalE),
            'balance' => floatval($totalI - $totalE)
        ];
    }
}

// Calcular totales generales
$totalIngresos = 0;
$totalEgresos = 0;
$totalBalance = 0;
foreach ($datosComparativa as $dato) {
    $totalIngresos += $dato['ingresos'];
    $totalEgresos += $dato['egresos'];
    $totalBalance += $dato['balance'];
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tituloReporte; ?> - IUM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none; }
            body { 
                padding: 20px;
                font-size: 12px;
            }
            .table { font-size: 11px; }
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            padding: 30px;
        }
        
        .reporte-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 0 auto;
        }
        
        .header-reporte {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #B80000;
        }
        
        .header-reporte img {
            max-width: 150px;
            margin-bottom: 15px;
        }
        
        .header-reporte h2 {
            color: #B80000;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .header-reporte .subtitle {
            color: #666;
            font-size: 14px;
        }
        
        .table-comparativa {
            margin-top: 30px;
        }
        
        .table-comparativa thead {
            background-color: #B80000;
            color: white;
        }
        
        .table-comparativa th {
            font-weight: 600;
            padding: 12px;
            text-align: center;
        }
        
        .table-comparativa td {
            padding: 10px;
            vertical-align: middle;
        }
        
        .text-ingreso { color: #28a745; font-weight: 600; }
        .text-egreso { color: #dc3545; font-weight: 600; }
        .text-balance-positivo { color: #28a745; font-weight: 700; }
        .text-balance-negativo { color: #dc3545; font-weight: 700; }
        
        .totales-row {
            background-color: #f8f9fa;
            font-weight: bold;
            border-top: 2px solid #B80000;
        }
        
        .footer-reporte {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        
        .btn-imprimir {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .resumen-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card-resumen {
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .card-ingreso { background-color: #d4edda; border-left: 4px solid #28a745; }
        .card-egreso { background-color: #f8d7da; border-left: 4px solid #dc3545; }
        .card-balance { background-color: #fff3cd; border-left: 4px solid #ffc107; }
        
        .card-resumen h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            font-weight: 600;
        }
        
        .card-resumen .monto {
            font-size: 24px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <button class="btn btn-danger btn-imprimir no-print" onclick="window.print()">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-printer" viewBox="0 0 16 16">
            <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
            <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
        </svg>
        Imprimir
    </button>

    <div class="reporte-container">
        <!-- Header -->
        <div class="header-reporte">
            <img src="public/logo ium blanco.png" alt="IUM Logo" onerror="this.style.display='none'" style="filter: brightness(0) invert(1);">
            <h2><?php echo htmlspecialchars($tituloReporte); ?></h2>
            <p class="subtitle">Instituto Universitario de Morelia</p>
            <p class="subtitle"><strong>Fecha de generación:</strong> <?php echo date('d/m/Y H:i'); ?></p>
        </div>

        <!-- Resumen en Cards -->
        <div class="resumen-cards">
            <div class="card-resumen card-ingreso">
                <h3>Total Ingresos</h3>
                <div class="monto text-ingreso"><?php echo '$' . number_format($totalIngresos, 2); ?></div>
            </div>
            <div class="card-resumen card-egreso">
                <h3>Total Egresos</h3>
                <div class="monto text-egreso"><?php echo '$' . number_format($totalEgresos, 2); ?></div>
            </div>
            <div class="card-resumen card-balance">
                <h3>Balance Total</h3>
                <div class="monto <?php echo $totalBalance >= 0 ? 'text-balance-positivo' : 'text-balance-negativo'; ?>">
                    <?php echo '$' . number_format($totalBalance, 2); ?>
                </div>
            </div>
        </div>

        <!-- Tabla de Comparativa -->
        <div class="table-comparativa">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 30%;">Mes</th>
                        <th style="width: 23%;">Ingresos</th>
                        <th style="width: 23%;">Egresos</th>
                        <th style="width: 24%;">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($datosComparativa)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay datos para mostrar</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($datosComparativa as $dato): ?>
                            <tr>
                                <td><strong><?php echo ucfirst($dato['mes']); ?></strong></td>
                                <td class="text-end text-ingreso">
                                    $<?php echo number_format($dato['ingresos'], 2); ?>
                                </td>
                                <td class="text-end text-egreso">
                                    $<?php echo number_format($dato['egresos'], 2); ?>
                                </td>
                                <td class="text-end <?php echo $dato['balance'] >= 0 ? 'text-balance-positivo' : 'text-balance-negativo'; ?>">
                                    $<?php echo number_format($dato['balance'], 2); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- Fila de totales -->
                        <tr class="totales-row">
                            <td>TOTALES</td>
                            <td class="text-end text-ingreso">
                                $<?php echo number_format($totalIngresos, 2); ?>
                            </td>
                            <td class="text-end text-egreso">
                                $<?php echo number_format($totalEgresos, 2); ?>
                            </td>
                            <td class="text-end <?php echo $totalBalance >= 0 ? 'text-balance-positivo' : 'text-balance-negativo'; ?>">
                                $<?php echo number_format($totalBalance, 2); ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer-reporte">
            <p><strong>Instituto Universitario de Morelia</strong></p>
            <p>Sistema ERP - Dashboard Ejecutivo</p>
            <p>Generado por: <?php echo htmlspecialchars($_SESSION['user_nombre'] ?? 'Usuario'); ?></p>
        </div>
    </div>

    <script>
        // Auto-imprimir al cargar (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
