<?php
// generate_comparativa_dashboard.php - Vista de impresión para comparativa de Ingresos vs Egresos

session_start();
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("No autorizado");
}

$meses = isset($_GET['meses']) ? intval($_GET['meses']) : 6;
$mesEspecifico = isset($_GET['mes']) ? intval($_GET['mes']) : null;
$anioEspecifico = isset($_GET['anio']) ? intval($_GET['anio']) : null;
$formato = $_GET['formato'] ?? 'html'; // 'html' o 'excel'

// Si se especifica mes y año, calcular fechas
if ($mesEspecifico && $anioEspecifico) {
    $fechaInicio = date('Y-m-01', strtotime("$anioEspecifico-$mesEspecifico-01"));
    $fechaFin = date('Y-m-t', strtotime("$anioEspecifico-$mesEspecifico-01"));
    $titulo = "Comparativa de " . date('F Y', strtotime($fechaInicio));
} else {
    // Calcular rango de fechas basado en meses
    $fechaFin = date('Y-m-t'); // Último día del mes actual
    $fechaInicio = date('Y-m-01', strtotime("-" . ($meses - 1) . " months"));
    $titulo = $meses === 1 ? "Comparativa del Último Mes" : "Comparativa de los Últimos $meses Meses";
}

// Obtener datos de ingresos por mes
$sqlIngresos = "SELECT DATE_FORMAT(fecha, '%Y-%m') as mes, 
                       DATE_FORMAT(fecha, '%M %Y') as mes_nombre,
                       SUM(monto) as total
                FROM ingresos 
                WHERE fecha BETWEEN ? AND ?
                GROUP BY DATE_FORMAT(fecha, '%Y-%m')
                ORDER BY mes ASC";

$stmt = $conn->prepare($sqlIngresos);
$stmt->bind_param("ss", $fechaInicio, $fechaFin);
$stmt->execute();
$resultIngresos = $stmt->get_result();
$ingresos = $resultIngresos->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener datos de egresos por mes
$sqlEgresos = "SELECT DATE_FORMAT(fecha, '%Y-%m') as mes,
                      DATE_FORMAT(fecha, '%M %Y') as mes_nombre,
                      SUM(monto) as total
               FROM egresos 
               WHERE fecha BETWEEN ? AND ?
               GROUP BY DATE_FORMAT(fecha, '%Y-%m')
               ORDER BY mes ASC";

$stmt = $conn->prepare($sqlEgresos);
$stmt->bind_param("ss", $fechaInicio, $fechaFin);
$stmt->execute();
$resultEgresos = $stmt->get_result();
$egresos = $resultEgresos->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Combinar datos en un array por mes
$datos = [];
$totalIngresos = 0;
$totalEgresos = 0;

// Crear array con todos los meses del rango
$mesesArray = [];
foreach ($ingresos as $ing) {
    if (!isset($datos[$ing['mes']])) {
        $datos[$ing['mes']] = [
            'mes' => $ing['mes'],
            'mes_nombre' => $ing['mes_nombre'],
            'ingresos' => 0,
            'egresos' => 0,
            'balance' => 0
        ];
    }
    $datos[$ing['mes']]['ingresos'] = floatval($ing['total']);
    $totalIngresos += floatval($ing['total']);
}

foreach ($egresos as $egr) {
    if (!isset($datos[$egr['mes']])) {
        $datos[$egr['mes']] = [
            'mes' => $egr['mes'],
            'mes_nombre' => $egr['mes_nombre'],
            'ingresos' => 0,
            'egresos' => 0,
            'balance' => 0
        ];
    }
    $datos[$egr['mes']]['egresos'] = floatval($egr['total']);
    $totalEgresos += floatval($egr['total']);
}

// Calcular balances
foreach ($datos as &$dato) {
    $dato['balance'] = $dato['ingresos'] - $dato['egresos'];
}

$balanceTotal = $totalIngresos - $totalEgresos;

// Si es Excel, generar CSV
if ($formato === 'excel') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=comparativa_dashboard_' . date('Y-m-d_His') . '.csv');
    
    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    
    $output = fopen('php://output', 'w');
    
    fputcsv($output, ['COMPARATIVA DE INGRESOS VS EGRESOS']);
    fputcsv($output, [$titulo]);
    fputcsv($output, ['Período: ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin))]);
    fputcsv($output, ['Generado: ' . date('d/m/Y H:i:s')]);
    fputcsv($output, []);
    
    fputcsv($output, ['Mes', 'Ingresos', 'Egresos', 'Balance']);
    
    foreach ($datos as $dato) {
        fputcsv($output, [
            $dato['mes_nombre'],
            '$' . number_format($dato['ingresos'], 2),
            '$' . number_format($dato['egresos'], 2),
            '$' . number_format($dato['balance'], 2)
        ]);
    }
    
    fputcsv($output, []);
    fputcsv($output, ['TOTALES']);
    fputcsv($output, ['Total Ingresos:', '$' . number_format($totalIngresos, 2)]);
    fputcsv($output, ['Total Egresos:', '$' . number_format($totalEgresos, 2)]);
    fputcsv($output, ['Balance Total:', '$' . number_format($balanceTotal, 2)]);
    
    fclose($output);
    exit;
}

// Si es HTML, generar página de impresión
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparativa Dashboard - <?php echo date('d/m/Y'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            .card { page-break-inside: avoid; }
        }
        body {
            background: white;
            padding: 20px;
        }
        .reporte-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #B80000;
        }
        .reporte-header h1 {
            color: #B80000;
            font-weight: bold;
        }
        .stats-card {
            border-left: 4px solid;
            margin-bottom: 20px;
        }
        .stats-card.ingresos { border-color: #28a745; }
        .stats-card.egresos { border-color: #dc3545; }
        .stats-card.balance { border-color: #B80000; }
    </style>
</head>
<body>
    <div class="reporte-header">
        <h1>📊 COMPARATIVA DE INGRESOS VS EGRESOS</h1>
        <h3><?php echo strtoupper($titulo); ?></h3>
        <p class="mb-0"><strong>Período:</strong> <?php echo date('d/m/Y', strtotime($fechaInicio)); ?> al <?php echo date('d/m/Y', strtotime($fechaFin)); ?></p>
        <p class="mb-0"><strong>Generado:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        <p class="mb-0"><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['user_nombre']); ?></p>
    </div>

    <!-- Formulario de búsqueda por mes/año (solo visible en pantalla) -->
    <div class="card mb-4 no-print">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0">🔍 Buscar Comparativa por Mes/Año</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row g-3">
                <input type="hidden" name="formato" value="html">
                <div class="col-md-4">
                    <label class="form-label">Mes</label>
                    <select name="mes" class="form-select" required>
                        <option value="">Seleccionar mes...</option>
                        <?php
                        $mesesNombres = [
                            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ];
                        foreach ($mesesNombres as $num => $nombre) {
                            $selected = ($mesEspecifico == $num) ? 'selected' : '';
                            echo "<option value='$num' $selected>$nombre</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Año</label>
                    <select name="anio" class="form-select" required>
                        <option value="">Seleccionar año...</option>
                        <?php
                        $anioActual = date('Y');
                        for ($i = $anioActual; $i >= $anioActual - 5; $i--) {
                            $selected = ($anioEspecifico == $i) ? 'selected' : '';
                            echo "<option value='$i' $selected>$i</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-danger me-2">Buscar</button>
                    <button type="button" onclick="location.href='?meses=<?php echo $meses; ?>&formato=html'" class="btn btn-secondary">Limpiar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tarjetas de resumen -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card stats-card ingresos">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Ingresos</h6>
                    <h3 class="text-success mb-0">$<?php echo number_format($totalIngresos, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card egresos">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Egresos</h6>
                    <h3 class="text-danger mb-0">$<?php echo number_format($totalEgresos, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card balance">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Balance Total</h6>
                    <h3 class="<?php echo $balanceTotal >= 0 ? 'text-success' : 'text-danger'; ?> mb-0">
                        $<?php echo number_format($balanceTotal, 2); ?>
                    </h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfica -->
    <div class="card mb-4">
        <div class="card-body">
            <canvas id="chartComparativa" style="max-height: 400px;"></canvas>
        </div>
    </div>

    <!-- Tabla de datos -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0">📋 Detalle por Mes</h5>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-danger">
                    <tr>
                        <th>Mes</th>
                        <th class="text-end">Ingresos</th>
                        <th class="text-end">Egresos</th>
                        <th class="text-end">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($datos)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted">No hay datos en este período</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($datos as $dato): ?>
                            <tr>
                                <td><strong><?php echo $dato['mes_nombre']; ?></strong></td>
                                <td class="text-end text-success">$<?php echo number_format($dato['ingresos'], 2); ?></td>
                                <td class="text-end text-danger">$<?php echo number_format($dato['egresos'], 2); ?></td>
                                <td class="text-end <?php echo $dato['balance'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                    <strong>$<?php echo number_format($dato['balance'], 2); ?></strong>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="table-secondary">
                            <td><strong>TOTALES</strong></td>
                            <td class="text-end text-success"><strong>$<?php echo number_format($totalIngresos, 2); ?></strong></td>
                            <td class="text-end text-danger"><strong>$<?php echo number_format($totalEgresos, 2); ?></strong></td>
                            <td class="text-end <?php echo $balanceTotal >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <strong>$<?php echo number_format($balanceTotal, 2); ?></strong>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Botones de acción -->
    <div class="no-print text-center mb-4">
        <button onclick="window.print()" class="btn btn-primary btn-lg me-2">
            🖨️ Imprimir
        </button>
        <button onclick="location.href='?meses=<?php echo $meses; ?>&mes=<?php echo $mesEspecifico; ?>&anio=<?php echo $anioEspecifico; ?>&formato=excel'" class="btn btn-success btn-lg me-2">
            📊 Exportar a Excel
        </button>
        <button onclick="window.close()" class="btn btn-secondary btn-lg">
            ❌ Cerrar
        </button>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('chartComparativa');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($datos, 'mes_nombre')); ?>,
                    datasets: [
                        {
                            label: 'Ingresos',
                            data: <?php echo json_encode(array_column($datos, 'ingresos')); ?>,
                            backgroundColor: 'rgba(40, 167, 69, 0.7)',
                            borderColor: 'rgba(40, 167, 69, 1)',
                            borderWidth: 2
                        },
                        {
                            label: 'Egresos',
                            data: <?php echo json_encode(array_column($datos, 'egresos')); ?>,
                            backgroundColor: 'rgba(220, 53, 69, 0.7)',
                            borderColor: 'rgba(220, 53, 69, 1)',
                            borderWidth: 2
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += '$' + context.parsed.y.toLocaleString('es-MX', {minimumFractionDigits: 2});
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString('es-MX');
                                }
                            }
                        }
                    }
                }
            });
        }
    });
    </script>
</body>
</html>
