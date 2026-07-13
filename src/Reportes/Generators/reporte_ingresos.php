<?php
// reporte_ingresos.php - Genera vista de impresión para reportes de ingresos

session_start();
require_once __DIR__ . '/../../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    die("No autorizado");
}

$tipo = $_GET['tipo'] ?? 'personalizado';
$fechaInicio = $_GET['fecha_inicio'] ?? null;
$fechaFin = $_GET['fecha_fin'] ?? null;
$formato = $_GET['formato'] ?? 'html'; // 'html' o 'excel'

// Calcular fechas según el tipo
if ($tipo === 'semanal') {
    $fechaFin = date('Y-m-d');
    $fechaInicio = date('Y-m-d', strtotime('-7 days'));
} elseif ($tipo === 'mensual') {
    $fechaFin = date('Y-m-d');
    $fechaInicio = date('Y-m-01');
}

if (!$fechaInicio || !$fechaFin) {
    die("Fechas no válidas");
}

// Obtener ingresos del rango
$sql = "SELECT i.*, c.nombre as nombre_categoria
    FROM ingresos i 
    LEFT JOIN categorias c ON i.id_categoria = c.id_categoria 
    WHERE i.fecha BETWEEN ? AND ? 
      AND COALESCE(i.estatus, 1) = 1
    ORDER BY i.fecha DESC";

$stmt = $conn->prepare($sql);
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

// Determinar título del reporte
$tituloTipo = '';
if ($tipo === 'semanal') {
    $tituloTipo = 'Últimos 7 días';
} elseif ($tipo === 'mensual') {
    $tituloTipo = 'Mes actual';
} else {
    $tituloTipo = 'Rango personalizado';
}

// Si es Excel, generar CSV
if ($formato === 'excel') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=reporte_ingresos_' . date('Y-m-d_His') . '.csv');
    
    // UTF-8 BOM para Excel
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    
    // Encabezado del reporte
    fputcsv($output, ['REPORTE DE INGRESOS - ' . strtoupper($tituloTipo)]);
    fputcsv($output, ['Período: ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin))]);
    fputcsv($output, ['Generado: ' . date('d/m/Y H:i:s')]);
    fputcsv($output, ['Usuario: ' . $_SESSION['user_nombre']]);
    fputcsv($output, []);
    
    // Encabezados de columnas
    fputcsv($output, ['Folio', 'Fecha', 'Categoría', 'Monto', 'Observaciones']);
    
    // Datos
    foreach ($ingresos as $ingreso) {
        fputcsv($output, [
            $ingreso['folio_ingreso'],
            date('d/m/Y', strtotime($ingreso['fecha'])),
            $ingreso['nombre_categoria'] ?? 'Sin categoría',
            '$' . number_format($ingreso['monto'], 2),
            $ingreso['observaciones'] ?? ''
        ]);
    }
    
    // Resumen
    fputcsv($output, []);
    fputcsv($output, ['RESUMEN']);
    fputcsv($output, ['Total de registros:', count($ingresos)]);
    fputcsv($output, ['Total general:', '$' . number_format($total, 2)]);
    fputcsv($output, []);
    fputcsv($output, ['TOTALES POR CATEGORÍA']);
    
    foreach ($porCategoria as $cat => $monto) {
        fputcsv($output, [$cat, '$' . number_format($monto, 2)]);
    }
    
    fclose($output);
    exit;
}

// Vista HTML para impresión
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ingresos - <?php echo $tituloTipo; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            padding: 20px; 
            font-family: Arial, sans-serif;
        }
        .reporte-header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
        }
        .reporte-header h1 {
            color: #2c3e50;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .reporte-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .reporte-info p {
            margin: 5px 0;
            font-size: 14px;
        }
        table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        table th {
            background: #2c3e50;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 13px;
        }
        table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            font-size: 12px;
        }
        table tbody tr:hover {
            background: #f8f9fa;
        }
        .resumen {
            background: #e9ecef;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .resumen h3 {
            font-size: 18px;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .resumen-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
        }
        .resumen-item {
            background: white;
            padding: 12px;
            border-radius: 6px;
            border-left: 4px solid #28a745;
        }
        .resumen-item strong {
            display: block;
            color: #666;
            font-size: 12px;
            margin-bottom: 5px;
        }
        .resumen-item span {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
        }
        .no-print {
            margin: 20px 0;
            text-align: center;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; page-break-after: auto; }
        }
        .text-success { color: #28a745; }
        .text-end { text-align: right; }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
        <button onclick="window.location.href='?tipo=<?php echo $tipo; ?>&fecha_inicio=<?php echo $fechaInicio; ?>&fecha_fin=<?php echo $fechaFin; ?>&formato=excel'" class="btn btn-success">📊 Exportar a Excel</button>
        <button onclick="window.close()" class="btn btn-secondary">❌ Cerrar</button>
    </div>

    <div class="reporte-header">
        <h1>📈 REPORTE DE INGRESOS</h1>
        <h2><?php echo strtoupper($tituloTipo); ?></h2>
    </div>

    <div class="reporte-info">
        <p><strong>Período:</strong> <?php echo date('d/m/Y', strtotime($fechaInicio)); ?> al <?php echo date('d/m/Y', strtotime($fechaFin)); ?></p>
        <p><strong>Fecha de generación:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        <p><strong>Generado por:</strong> <?php echo htmlspecialchars($_SESSION['user_nombre']); ?> (<?php echo htmlspecialchars($_SESSION['user_username']); ?>)</p>
    </div>

    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Folio</th>
                <th>Fecha</th>
                <th>Categoría</th>
                <th class="text-end">Monto</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($ingresos)): ?>
                <tr>
                    <td colspan="5" class="text-center">No hay ingresos registrados en este período</td>
                </tr>
            <?php else: ?>
                <?php foreach ($ingresos as $ingreso): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($ingreso['folio_ingreso']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($ingreso['fecha'])); ?></td>
                        <td><?php echo htmlspecialchars($ingreso['nombre_categoria'] ?? 'Sin categoría'); ?></td>
                        <td class="text-end text-success">$<?php echo number_format($ingreso['monto'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($porCategoria)): ?>
        <div class="grafica-section" style="margin-bottom: 30px; page-break-inside: avoid;">
            <h3 style="text-align: center; margin-bottom: 20px;">📊 Distribución por Categoría</h3>
            <canvas id="chartIngresos" style="max-height: 300px; margin: 0 auto; display: block;"></canvas>
        </div>
    <?php endif; ?>

    <div class="resumen">
        <h3>📊 Resumen General</h3>
        <div class="resumen-grid">
            <div class="resumen-item">
                <strong>Total de Registros</strong>
                <span><?php echo count($ingresos); ?></span>
            </div>
            <div class="resumen-item">
                <strong>Total General</strong>
                <span class="text-success">$<?php echo number_format($total, 2); ?></span>
            </div>
        </div>

        <?php if (!empty($porCategoria)): ?>
            <h3 style="margin-top: 20px;">📋 Totales por Categoría</h3>
            <div class="resumen-grid">
                <?php foreach ($porCategoria as $categoria => $monto): ?>
                    <div class="resumen-item">
                        <strong><?php echo htmlspecialchars($categoria); ?></strong>
                        <span class="text-success">$<?php echo number_format($monto, 2); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="no-print" style="margin-top: 30px;">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
        <button onclick="window.location.href='?tipo=<?php echo $tipo; ?>&fecha_inicio=<?php echo $fechaInicio; ?>&fecha_fin=<?php echo $fechaFin; ?>&formato=excel'" class="btn btn-success">📊 Exportar a Excel</button>
        <button onclick="window.close()" class="btn btn-secondary">❌ Cerrar</button>
    </div>

    <?php if (!empty($porCategoria)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('chartIngresos');
        if (ctx) {
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_keys($porCategoria)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($porCategoria)); ?>,
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)',
                            'rgba(255, 159, 64, 0.8)',
                            'rgba(199, 199, 199, 0.8)',
                            'rgba(83, 102, 255, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'right'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': $' + context.parsed.toLocaleString('es-MX', {minimumFractionDigits: 2});
                                }
                            }
                        }
                    }
                }
            });
        }
    });
    </script>
    <?php endif; ?>
</body>
</html>
