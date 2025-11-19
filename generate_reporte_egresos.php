<?php
// generate_reporte_egresos.php - Genera vista de impresión para reportes de egresos

session_start();
require_once 'db.php';

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

// Obtener egresos del rango
$sql = "SELECT e.*, c.nombre as nombre_categoria
        FROM egresos e 
        LEFT JOIN categorias c ON e.id_categoria = c.id_categoria 
        WHERE e.fecha BETWEEN ? AND ? 
        ORDER BY e.fecha DESC";

$stmt = $conn->prepare($sql);
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
    header('Content-Disposition: attachment; filename=reporte_egresos_' . date('Y-m-d_His') . '.csv');
    
    // UTF-8 BOM para Excel
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    
    // Encabezado del reporte
    fputcsv($output, ['REPORTE DE EGRESOS - ' . strtoupper($tituloTipo)]);
    fputcsv($output, ['Período: ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin))]);
    fputcsv($output, ['Generado: ' . date('d/m/Y H:i:s')]);
    fputcsv($output, ['Usuario: ' . $_SESSION['user_nombre']]);
    fputcsv($output, []);
    
    // Encabezados de columnas
    fputcsv($output, ['Folio', 'Fecha', 'Categoría', 'Concepto', 'Monto', 'Observaciones']);
    
    // Datos
    foreach ($egresos as $egreso) {
        fputcsv($output, [
            $egreso['folio_egreso'],
            date('d/m/Y', strtotime($egreso['fecha'])),
            $egreso['nombre_categoria'] ?? 'Sin categoría',
            $egreso['concepto'],
            '$' . number_format($egreso['monto'], 2),
            $egreso['observaciones'] ?? ''
        ]);
    }
    
    // Resumen
    fputcsv($output, []);
    fputcsv($output, ['RESUMEN']);
    fputcsv($output, ['Total de registros:', count($egresos)]);
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
    <title>Reporte de Egresos - <?php echo $tituloTipo; ?></title>
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
            border-left: 4px solid #dc3545;
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
        .text-danger { color: #dc3545; }
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
        <h1>📉 REPORTE DE EGRESOS</h1>
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
                <th>Concepto</th>
                <th class="text-end">Monto</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($egresos)): ?>
                <tr>
                    <td colspan="5" class="text-center">No hay egresos registrados en este período</td>
                </tr>
            <?php else: ?>
                <?php foreach ($egresos as $egreso): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($egreso['folio_egreso']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($egreso['fecha'])); ?></td>
                        <td><?php echo htmlspecialchars($egreso['nombre_categoria'] ?? 'Sin categoría'); ?></td>
                        <td><?php echo htmlspecialchars($egreso['concepto']); ?></td>
                        <td class="text-end text-danger">$<?php echo number_format($egreso['monto'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($porCategoria)): ?>
        <div class="grafica-section" style="margin-bottom: 30px; page-break-inside: avoid;">
            <h3 style="text-align: center; margin-bottom: 20px;">📊 Distribución por Categoría</h3>
            <canvas id="chartEgresos" style="max-height: 300px; margin: 0 auto; display: block;"></canvas>
        </div>
    <?php endif; ?>

    <div class="resumen">
        <h3>📊 Resumen General</h3>
        <div class="resumen-grid">
            <div class="resumen-item">
                <strong>Total de Registros</strong>
                <span><?php echo count($egresos); ?></span>
            </div>
            <div class="resumen-item">
                <strong>Total General</strong>
                <span class="text-danger">$<?php echo number_format($total, 2); ?></span>
            </div>
        </div>

        <?php if (!empty($porCategoria)): ?>
            <h3 style="margin-top: 20px;">📋 Totales por Categoría</h3>
            <div class="resumen-grid">
                <?php foreach ($porCategoria as $categoria => $monto): ?>
                    <div class="resumen-item">
                        <strong><?php echo htmlspecialchars($categoria); ?></strong>
                        <span class="text-danger">$<?php echo number_format($monto, 2); ?></span>
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
        const ctx = document.getElementById('chartEgresos');
        if (ctx) {
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: <?php echo json_encode(array_keys($porCategoria)); ?>,
                    datasets: [{
                        data: <?php echo json_encode(array_values($porCategoria)); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
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
