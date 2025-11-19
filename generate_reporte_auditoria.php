<?php
// generate_reporte_auditoria.php - Genera vista de impresión para reportes de auditoría

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

// Obtener registros de auditoría del rango
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

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $fechaInicio, $fechaFin);
$stmt->execute();
$result = $stmt->get_result();
$registros = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calcular estadísticas
$totalRegistros = count($registros);
$porSeccion = [];
$porAccion = [];
$porUsuario = [];

foreach ($registros as $reg) {
    // Extraer usuario del JSON
    $usuarioNombre = 'Sistema';
    if (!empty($reg['new_valor'])) {
        $jsonData = json_decode($reg['new_valor'], true);
        if (isset($jsonData['nombre'])) {
            $usuarioNombre = $jsonData['nombre'];
        }
    }
    
    // Por sección
    $seccion = $reg['seccion'] ?? 'Sin sección';
    if (!isset($porSeccion[$seccion])) {
        $porSeccion[$seccion] = 0;
    }
    $porSeccion[$seccion]++;
    
    // Por acción
    $accion = $reg['accion'] ?? 'Sin acción';
    if (!isset($porAccion[$accion])) {
        $porAccion[$accion] = 0;
    }
    $porAccion[$accion]++;
    
    // Por usuario
    if (!isset($porUsuario[$usuarioNombre])) {
        $porUsuario[$usuarioNombre] = 0;
    }
    $porUsuario[$usuarioNombre]++;
}

// Ordenar arrays
arsort($porSeccion);
arsort($porAccion);
arsort($porUsuario);

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
    header('Content-Disposition: attachment; filename=reporte_auditoria_' . date('Y-m-d_His') . '.csv');
    
    // UTF-8 BOM para Excel
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    
    // Encabezado del reporte
    fputcsv($output, ['REPORTE DE AUDITORÍA - ' . strtoupper($tituloTipo)]);
    fputcsv($output, ['Período: ' . date('d/m/Y', strtotime($fechaInicio)) . ' al ' . date('d/m/Y', strtotime($fechaFin))]);
    fputcsv($output, ['Generado: ' . date('d/m/Y H:i:s')]);
    fputcsv($output, ['Usuario: ' . $_SESSION['user_nombre']]);
    fputcsv($output, []);
    
    // Encabezados de columnas
    fputcsv($output, ['Fecha/Hora', 'Usuario', 'Sección', 'Acción', 'Detalles']);
    
    // Datos
    foreach ($registros as $reg) {
        // Extraer usuario del JSON
        $usuarioNombre = 'Sistema';
        if (!empty($reg['new_valor'])) {
            $jsonData = json_decode($reg['new_valor'], true);
            if (isset($jsonData['nombre'])) {
                $usuarioNombre = $jsonData['nombre'];
            }
        }
        
        // Formatear detalles
        $detalles = '';
        if (!empty($reg['old_valor']) && !empty($reg['new_valor'])) {
            $detalles = 'Cambio de valores';
        } elseif (!empty($reg['new_valor'])) {
            $detalles = 'Nuevo registro';
        } elseif (!empty($reg['old_valor'])) {
            $detalles = 'Registro eliminado';
        }
        
        fputcsv($output, [
            $reg['fecha_hora'],
            $usuarioNombre,
            $reg['seccion'] ?? '',
            $reg['accion'] ?? '',
            $detalles
        ]);
    }
    
    // Resumen
    fputcsv($output, []);
    fputcsv($output, ['RESUMEN ESTADÍSTICO']);
    fputcsv($output, ['Total de registros:', $totalRegistros]);
    fputcsv($output, []);
    
    fputcsv($output, ['REGISTROS POR SECCIÓN']);
    foreach ($porSeccion as $sec => $count) {
        fputcsv($output, [$sec, $count]);
    }
    fputcsv($output, []);
    
    fputcsv($output, ['REGISTROS POR ACCIÓN']);
    foreach ($porAccion as $acc => $count) {
        fputcsv($output, [$acc, $count]);
    }
    fputcsv($output, []);
    
    fputcsv($output, ['ACTIVIDAD POR USUARIO']);
    foreach ($porUsuario as $usr => $count) {
        fputcsv($output, [$usr, $count]);
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
    <title>Reporte de Auditoría - <?php echo $tituloTipo; ?></title>
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
            font-size: 11px;
        }
        table td {
            padding: 6px 8px;
            border-bottom: 1px solid #ddd;
            font-size: 10px;
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
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
        }
        .resumen-section {
            background: white;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #007bff;
        }
        .resumen-section h4 {
            font-size: 14px;
            margin-bottom: 12px;
            color: #2c3e50;
        }
        .stat-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #e9ecef;
            font-size: 12px;
        }
        .stat-item:last-child {
            border-bottom: none;
        }
        .stat-item strong {
            color: #666;
        }
        .stat-item span {
            font-weight: bold;
            color: #007bff;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 9px;
            border-radius: 4px;
            font-weight: bold;
        }
        .badge-registro { background: #28a745; color: white; }
        .badge-actualizacion { background: #ffc107; color: #000; }
        .badge-eliminacion { background: #dc3545; color: white; }
        .badge-login { background: #17a2b8; color: white; }
        .badge-logout { background: #6c757d; color: white; }
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
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
        <button onclick="window.location.href='?tipo=<?php echo $tipo; ?>&fecha_inicio=<?php echo $fechaInicio; ?>&fecha_fin=<?php echo $fechaFin; ?>&formato=excel'" class="btn btn-success">📊 Exportar a Excel</button>
        <button onclick="window.close()" class="btn btn-secondary">❌ Cerrar</button>
    </div>

    <div class="reporte-header">
        <h1>🔍 HISTORIAL DE AUDITORÍA</h1>
        <h2><?php echo strtoupper($tituloTipo); ?></h2>
    </div>

    <div class="reporte-info">
        <p><strong>Período:</strong> <?php echo date('d/m/Y', strtotime($fechaInicio)); ?> al <?php echo date('d/m/Y', strtotime($fechaFin)); ?></p>
        <p><strong>Fecha de generación:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
        <p><strong>Generado por:</strong> <?php echo htmlspecialchars($_SESSION['user_nombre']); ?> (<?php echo htmlspecialchars($_SESSION['user_username']); ?>)</p>
        <p><strong>Total de registros:</strong> <?php echo $totalRegistros; ?></p>
    </div>

    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Usuario</th>
                <th>Sección</th>
                <th>Acción</th>
                <th>Detalles</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($registros)): ?>
                <tr>
                    <td colspan="6" class="text-center">No hay registros de auditoría en este período</td>
                </tr>
            <?php else: ?>
                <?php foreach ($registros as $reg): ?>
                    <?php
                        $accion = strtolower($reg['accion'] ?? '');
                        $badgeClass = 'badge-registro';
                        if (strpos($accion, 'actualiza') !== false || strpos($accion, 'edita') !== false) {
                            $badgeClass = 'badge-actualizacion';
                        } elseif (strpos($accion, 'elimina') !== false || strpos($accion, 'borra') !== false) {
                            $badgeClass = 'badge-eliminacion';
                        } elseif (strpos($accion, 'login') !== false || strpos($accion, 'ingres') !== false) {
                            $badgeClass = 'badge-login';
                        } elseif (strpos($accion, 'logout') !== false || strpos($accion, 'sal') !== false) {
                            $badgeClass = 'badge-logout';
                        }
                    ?>
                    <?php
                        // Extraer usuario del JSON
                        $usuarioNombre = 'Sistema';
                        if (!empty($reg['new_valor'])) {
                            $jsonData = json_decode($reg['new_valor'], true);
                            if (isset($jsonData['nombre'])) {
                                $usuarioNombre = $jsonData['nombre'];
                            }
                        }
                        
                        // Formatear detalles
                        $detalles = '';
                        if (!empty($reg['old_valor']) && !empty($reg['new_valor'])) {
                            $detalles = 'Cambio de valores';
                        } elseif (!empty($reg['new_valor'])) {
                            $detalles = 'Nuevo registro';
                        } elseif (!empty($reg['old_valor'])) {
                            $detalles = 'Registro eliminado';
                        }
                    ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($reg['fecha_hora'])); ?></td>
                        <td><?php echo date('H:i:s', strtotime($reg['fecha_hora'])); ?></td>
                        <td><?php echo htmlspecialchars($usuarioNombre); ?></td>
                        <td><?php echo htmlspecialchars($reg['seccion'] ?? ''); ?></td>
                        <td><span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($reg['accion'] ?? ''); ?></span></td>
                        <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($detalles); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if (!empty($registros)): ?>
        <div class="resumen">
            <h3>📊 Resumen Estadístico</h3>
            <div class="resumen-grid">
                <!-- Registros por sección -->
                <div class="resumen-section">
                    <h4>📁 Registros por Sección</h4>
                    <?php foreach (array_slice($porSeccion, 0, 10) as $sec => $count): ?>
                        <div class="stat-item">
                            <strong><?php echo htmlspecialchars($sec); ?></strong>
                            <span><?php echo $count; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Registros por acción -->
                <div class="resumen-section" style="border-color: #28a745;">
                    <h4>⚡ Registros por Acción</h4>
                    <?php foreach (array_slice($porAccion, 0, 10) as $acc => $count): ?>
                        <div class="stat-item">
                            <strong><?php echo htmlspecialchars($acc); ?></strong>
                            <span><?php echo $count; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Actividad por usuario -->
                <div class="resumen-section" style="border-color: #ffc107;">
                    <h4>👤 Actividad por Usuario</h4>
                    <?php foreach (array_slice($porUsuario, 0, 10) as $usr => $count): ?>
                        <div class="stat-item">
                            <strong><?php echo htmlspecialchars($usr); ?></strong>
                            <span><?php echo $count; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="no-print" style="margin-top: 30px;">
        <button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
        <button onclick="window.location.href='?tipo=<?php echo $tipo; ?>&fecha_inicio=<?php echo $fechaInicio; ?>&fecha_fin=<?php echo $fechaFin; ?>&formato=excel'" class="btn btn-success">📊 Exportar a Excel</button>
        <button onclick="window.close()" class="btn btn-secondary">❌ Cerrar</button>
    </div>
</body>
</html>
