<?php
// generate_reporte_consolidado.php - Genera vista de impresión para reporte consolidado

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

// Obtener totales de ingresos
$sqlIngresos = "SELECT SUM(monto) as total, COUNT(*) as cantidad FROM ingresos WHERE fecha BETWEEN ? AND ?";
$stmt = $conn->prepare($sqlIngresos);
$stmt->bind_param("ss", $fechaInicio, $fechaFin);
$stmt->execute();
$resultIngresos = $stmt->get_result()->fetch_assoc();
$totalIngresos = floatval($resultIngresos['total'] ?? 0);
$cantidadIngresos = intval($resultIngresos['cantidad'] ?? 0);
$stmt->close();

// Obtener totales de egresos
$sqlEgresos = "SELECT SUM(monto) as total, COUNT(*) as cantidad FROM egresos WHERE fecha BETWEEN ? AND ?";
$stmt = $conn->prepare($sqlEgresos);
$stmt->bind_param("ss", $fechaInicio, $fechaFin);
$stmt->execute();
$resultEgresos = $stmt->get_result()->fetch_assoc();
$totalEgresos = floatval($resultEgresos['total'] ?? 0);
$cantidadEgresos = intval($resultEgresos['cantidad'] ?? 0);
$stmt->close();

// Obtener ingresos por categoría
$sqlIngresosCat = "SELECT c.nombre, SUM(i.monto) as total 
                   FROM ingresos i 
                   LEFT JOIN categorias c ON i.id_categoria = c.id_categoria 
                   WHERE i.fecha BETWEEN ? AND ? 
                   GROUP BY i.id_categoria, c.nombre 
                   ORDER BY total DESC";
$stmt = $conn->prepare($sqlIngresosCat);
$stmt->bind_param("ss", $fechaInicio, $fechaFin);
$stmt->execute();
$ingresosPorCategoria = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Obtener egresos por categoría
$sqlEgresosCat = "SELECT c.nombre, SUM(e.monto) as total 
                  FROM egresos e 
                  LEFT JOIN categorias c ON e.id_categoria = c.id_categoria 
                  WHERE e.fecha BETWEEN ? AND ? 
                  GROUP BY e.id_categoria, c.nombre 
                  ORDER BY total DESC";
$stmt = $conn->prepare($sqlEgresosCat);
$stmt->bind_param("ss", $fechaInicio, $fechaFin);
$stmt->execute();
$egresosPorCategoria = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$balance = $totalIngresos - $totalEgresos;

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
    header('Content-Disposition: attachment; filename=reporte_consolidado_' . date('Y-m-d_His') . '.csv');
    
    // UTF-8 BOM para Excel
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    
    // Encabezado del reporte
    fputcsv($output, ['REPORTE CONSOLIDADO - ' . strtoupper($tituloTipo)]);
    fputcsv($output, ['Período: ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin))]);
    fputcsv($output, ['Generado: ' . date('d/m/Y H:i:s')]);
    fputcsv($output, ['Usuario: ' . $_SESSION['user_nombre']]);
    fputcsv($output, []);
    
    // Resumen general
    fputcsv($output, ['RESUMEN GENERAL']);
    fputcsv($output, ['Concepto', 'Cantidad', 'Total']);
    fputcsv($output, ['Ingresos', $cantidadIngresos, '$' . number_format($totalIngresos, 2)]);
    fputcsv($output, ['Egresos', $cantidadEgresos, '$' . number_format($totalEgresos, 2)]);
    fputcsv($output, ['Balance', '', '$' . number_format($balance, 2)]);
    fputcsv($output, []);
    
    // Ingresos por categoría
    fputcsv($output, ['INGRESOS POR CATEGORÍA']);
    fputcsv($output, ['Categoría', 'Total']);
    foreach ($ingresosPorCategoria as $cat) {
        fputcsv($output, [$cat['nombre'] ?? 'Sin categoría', '$' . number_format($cat['total'], 2)]);
    }
    fputcsv($output, []);
    
    // Egresos por categoría
    fputcsv($output, ['EGRESOS POR CATEGORÍA']);
    fputcsv($output, ['Categoría', 'Total']);
    foreach ($egresosPorCategoria as $cat) {
        fputcsv($output, [$cat['nombre'] ?? 'Sin categoría', '$' . number_format($cat['total'], 2)]);
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
    <title>Reporte Consolidado - <?php echo $tituloTipo; ?></title>
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
        .resumen-principal {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card-resumen {
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
        }
        .card-resumen.ingresos { border-color: #28a745; }
        .card-resumen.egresos { border-color: #dc3545; }
        .card-resumen.balance { border-color: #007bff; }
        .card-resumen h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .card-resumen .valor {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .card-resumen .cantidad {
            font-size: 12px;
            color: #999;
        }
        .card-resumen.ingresos .valor { color: #28a745; }
        .card-resumen.egresos .valor { color: #dc3545; }
        .card-resumen.balance .valor { color: #007bff; }
        .categorias-section {
            margin-top: 30px;
        }
        .categorias-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .categoria-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .categoria-card h4 {
            font-size: 16px;
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .categoria-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        .categoria-item:last-child {
            border-bottom: none;
        }
        .categoria-item span:first-child {
            color: #666;
            font-size: 13px;
        }
        .categoria-item span:last-child {
            font-weight: bold;
            font-size: 14px;
        }
        .no-print {
            margin: 20px 0;
            text-align: center;
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
        .text-success { color: #28a745; }
        .text-danger { color: #dc3545; }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
        <button onclick="window.location.href='?tipo=<?php echo $tipo; ?>&fecha_inicio=<?php echo $fechaInicio; ?>&fecha_fin=<?php echo $fechaFin; ?>&formato=excel'" class="btn btn-success">📊 Exportar a Excel</button>
        <button onclick="window.close()" class="btn btn-secondary">❌ Cerrar</button>
    </div>

    <div class="reporte-header">
        <h1>📊 REPORTE CONSOLIDADO</h1>
        <h2><?php echo strtoupper($tituloTipo); ?></h2>
    </div>

    <div class="reporte-info">
        <p><strong>Período:</strong> <?php echo date('d/m/Y', strtotime($fechaInicio)); ?> al <?php echo date('d/m/Y', strtotime($fechaFin)); ?></p>
        <p><strong>Fecha de generación:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        <p><strong>Generado por:</strong> <?php echo htmlspecialchars($_SESSION['user_nombre']); ?> (<?php echo htmlspecialchars($_SESSION['user_username']); ?>)</p>
    </div>

    <div class="resumen-principal">
        <div class="card-resumen ingresos">
            <h3>💰 Ingresos Totales</h3>
            <div class="valor">$<?php echo number_format($totalIngresos, 2); ?></div>
            <div class="cantidad"><?php echo $cantidadIngresos; ?> registro<?php echo $cantidadIngresos != 1 ? 's' : ''; ?></div>
        </div>

        <div class="card-resumen egresos">
            <h3>💸 Egresos Totales</h3>
            <div class="valor">$<?php echo number_format($totalEgresos, 2); ?></div>
            <div class="cantidad"><?php echo $cantidadEgresos; ?> registro<?php echo $cantidadEgresos != 1 ? 's' : ''; ?></div>
        </div>

        <div class="card-resumen balance">
            <h3>📈 Balance Final</h3>
            <div class="valor">$<?php echo number_format($balance, 2); ?></div>
            <div class="cantidad"><?php echo $balance >= 0 ? 'Superávit' : 'Déficit'; ?></div>
        </div>
    </div>

    <div class="grafica-section" style="margin: 30px 0; page-break-inside: avoid;">
        <h3 style="text-align: center; margin-bottom: 20px;">📊 Comparativa Financiera</h3>
        <canvas id="chartConsolidado" style="max-height: 300px; margin: 0 auto; display: block;"></canvas>
    </div>

    <div class="categorias-section">
        <div class="categorias-grid">
            <div class="categoria-card" style="border-color: #28a745;">
                <h4>📈 Ingresos por Categoría</h4>
                <?php if (empty($ingresosPorCategoria)): ?>
                    <p style="color: #999; font-size: 13px;">No hay ingresos registrados</p>
                <?php else: ?>
                    <?php foreach ($ingresosPorCategoria as $cat): ?>
                        <div class="categoria-item">
                            <span><?php echo htmlspecialchars($cat['nombre'] ?? 'Sin categoría'); ?></span>
                            <span class="text-success">$<?php echo number_format($cat['total'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="categoria-card" style="border-color: #dc3545;">
                <h4>📉 Egresos por Categoría</h4>
                <?php if (empty($egresosPorCategoria)): ?>
                    <p style="color: #999; font-size: 13px;">No hay egresos registrados</p>
                <?php else: ?>
                    <?php foreach ($egresosPorCategoria as $cat): ?>
                        <div class="categoria-item">
                            <span><?php echo htmlspecialchars($cat['nombre'] ?? 'Sin categoría'); ?></span>
                            <span class="text-danger">$<?php echo number_format($cat['total'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="no-print" style="margin-top: 30px;">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
        <button onclick="window.location.href='?tipo=<?php echo $tipo; ?>&fecha_inicio=<?php echo $fechaInicio; ?>&fecha_fin=<?php echo $fechaFin; ?>&formato=excel'" class="btn btn-success">📊 Exportar a Excel</button>
        <button onclick="window.close()" class="btn btn-secondary">❌ Cerrar</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('chartConsolidado');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Ingresos', 'Egresos', 'Balance'],
                    datasets: [{
                        label: 'Montos',
                        data: [<?php echo $totalIngresos; ?>, <?php echo $totalEgresos; ?>, <?php echo abs($balance); ?>],
                        backgroundColor: [
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(255, 99, 132, 0.8)',
                            <?php echo $balance >= 0 ? "'rgba(54, 162, 235, 0.8)'" : "'rgba(255, 159, 64, 0.8)'"; ?>
                        ],
                        borderColor: [
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 99, 132, 1)',
                            <?php echo $balance >= 0 ? "'rgba(54, 162, 235, 1)'" : "'rgba(255, 159, 64, 1)'"; ?>
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Monto: $' + context.parsed.y.toLocaleString('es-MX', {minimumFractionDigits: 2});
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
