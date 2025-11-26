<?php
/**
 * Recibo de Ingreso - Inscripción y Reinscripción
 */

require_once __DIR__ . '/../../../config/database.php';

$folio = isset($_GET['folio']) ? (int)$_GET['folio'] : 0;
$reimpresion = isset($_GET['reimpresion']) ? (int)$_GET['reimpresion'] : 0;

if ($folio <= 0) {
    http_response_code(400);
    echo "Folio inválido.";
    exit;
}

// Eliminado campo concepto
$sql = "SELECT i.*, c.nombre AS nombre_categoria 
        FROM ingresos i
        LEFT JOIN categorias c ON i.id_categoria = c.id_categoria 
        WHERE i.folio_ingreso = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Error en la consulta: " . htmlspecialchars($conn->error);
    exit;
}
$stmt->bind_param("i", $folio);
$stmt->execute();
$res = $stmt->get_result();
$ingreso = $res->fetch_assoc();
$stmt->close();

if (!$ingreso) {
    echo "Recibo no encontrado.";
    exit;
}

$sqlPagos = "SELECT * FROM pagos_parciales WHERE folio_ingreso = ? ORDER BY orden ASC";
$stmtPagos = $conn->prepare($sqlPagos);
$pagosParciales = [];
if ($stmtPagos) {
    $stmtPagos->bind_param("i", $folio);
    $stmtPagos->execute();
    $resPagos = $stmtPagos->get_result();
    while ($pago = $resPagos->fetch_assoc()) {
        $pagosParciales[] = $pago;
    }
    $stmtPagos->close();
}

$monto = (float)($ingreso['monto'] ?? 0);
$montoFormateado = '$' . number_format($monto, 2);
if (class_exists('NumberFormatter')) {
    try {
        $fmt = new NumberFormatter('es_MX', NumberFormatter::CURRENCY);
        $montoFormateado = $fmt->formatCurrency($monto, 'MXN');
    } catch (Exception $e) {}
}

$cantidadConLetra = '';
if (class_exists('NumberFormatter')) {
    try {
        $entero = floor($monto);
        $centavos = round(($monto - $entero) * 100);
        $fmtSpell = new NumberFormatter('es_MX', NumberFormatter::SPELLOUT);
        $letras = strtoupper($fmtSpell->format($entero));
        $cantidadConLetra = $letras . ' PESOS ' . sprintf('%02d', $centavos) . '/100 M.N.';
    } catch (Exception $e) {}
}

$logoPath = '../../../public/logo ium blanco.png';
$fecha = htmlspecialchars($ingreso['fecha'] ?? '');
$folioEsc = htmlspecialchars($ingreso['folio_ingreso'] ?? '');
$alumno = htmlspecialchars($ingreso['alumno'] ?? '');
$matricula = htmlspecialchars($ingreso['matricula'] ?? '');
$nivel = htmlspecialchars($ingreso['nivel'] ?? '');
$programa = htmlspecialchars($ingreso['programa'] ?? '');
$grado = htmlspecialchars($ingreso['grado'] ?? '');
$modalidad = htmlspecialchars($ingreso['modalidad'] ?? '');
$categoria = htmlspecialchars($ingreso['nombre_categoria'] ?? '');
$anio = htmlspecialchars($ingreso['anio'] ?? '');
$metodo = htmlspecialchars($ingreso['metodo_de_pago'] ?? '');
$observaciones = htmlspecialchars($ingreso['observaciones'] ?? '');

$detalleMetodos = '';
if (!empty($pagosParciales)) {
    foreach ($pagosParciales as $pago) {
        $metodoPago = htmlspecialchars($pago['metodo_pago']);
        $montoPago = number_format((float)$pago['monto'], 2);
        $detalleMetodos .= '<div style="padding: 2px 0; font-size: 11px;"><strong>' . $metodoPago . ':</strong> $' . $montoPago . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo <?php echo $categoria; ?> #<?php echo $folioEsc; ?></title>
    <style>
        @page { size: 8.5in 5.5in; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 7px; line-height: 1.2; }
        .page { width: 8.5in; height: 5.5in; padding: 0.15in 0.2in; position: relative; background: white; display: flex; flex-direction: column; }
        
        .header { display: table; width: 100%; margin-bottom: 8px; }
        .header-left { display: table-cell; width: 30%; vertical-align: top; }
        .header-right { display: table-cell; width: 70%; vertical-align: top; text-align: right; }
        .logo-box { display: inline-block; background: #9e1b32; padding: 4px 8px; border-radius: 3px; }
        .logo-box img { height: 32px; vertical-align: middle; }
        .institution { font-size: 7px; color: #333; margin-top: 2px; font-weight: bold; }
        .doc-title { font-size: 13px; font-weight: bold; color: #1a1a1a; margin-bottom: 1px; }
        .doc-subtitle { font-size: 10px; color: #9e1b32; font-weight: bold; margin-bottom: 2px; }
        .folio { font-size: 11px; color: #9e1b32; font-weight: bold; }
        
        .divider { height: 2px; background: #9e1b32; margin: 6px 0; }
        
        .content { flex: 1; display: flex; flex-direction: column; }
        
        .grid { display: table; width: 100%; margin-bottom: 6px; }
        .grid-row { display: table-row; }
        .grid-cell { display: table-cell; padding: 3px 6px 3px 0; vertical-align: top; }
        .grid-cell.full { width: 100%; }
        .grid-cell.half { width: 50%; }
        
        .label { font-size: 7px; color: #666; font-weight: bold; text-transform: uppercase; display: block; margin-bottom: 1px; }
        .value { font-size: 9px; color: #000; border-bottom: 1px solid #ddd; padding-bottom: 2px; min-height: 14px; }
        
        .monto-section { background: #f8f9fa; border: 2px solid #9e1b32; padding: 8px; text-align: center; margin: 8px 0; border-radius: 4px; }
        .monto-label { font-size: 8px; color: #666; font-weight: bold; margin-bottom: 3px; }
        .monto-value { font-size: 20px; font-weight: bold; color: #9e1b32; line-height: 1.2; }
        .monto-currency { font-size: 8px; color: #666; margin-top: 2px; }
        
        .letra-box { background: #fff8dc; border: 1px solid #daa520; padding: 6px; margin: 6px 0; border-radius: 3px; }
        .letra-text { font-size: 7px; font-style: italic; color: #333; line-height: 1.3; }
        
        .payment-box { background: #f5f5f5; border: 1px solid #ddd; padding: 6px; margin: 6px 0; border-radius: 3px; font-size: 7px; }
        .description-box { border: 1px solid #ddd; padding: 8px; min-height: 45px; background: #fafafa; margin: 6px 0; border-radius: 3px; font-size: 7px; flex: 1; }
        
        .signature-section { margin-top: auto; padding-top: 20px; text-align: center; }
        .signature-line { border-top: 1px solid #333; width: 55%; margin: 0 auto 6px auto; }
        .signature-label { font-size: 9px; font-weight: bold; color: #333; }
        .signature-name { font-size: 10px; color: #000; margin-top: 3px; font-weight: bold; }
        
        .footer { font-size: 7px; color: #888; text-align: center; border-top: 1px solid #eee; padding-top: 4px; margin-top: 8px; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 70px; color: rgba(220, 53, 69, 0.12); font-weight: bold; z-index: 0; pointer-events: none; }
        
        @media print { body { margin: 0; } .no-print { display: none; } .page { box-shadow: none; } }
    </style>
</head>
<body>
    <?php if ($reimpresion): ?>
        <div class="watermark">REIMPRESIÓN</div>
    <?php endif; ?>
    
    <div class="page">
        <div class="header">
            <div class="header-left">
                <div class="logo-box">
                    <img src="<?php echo $logoPath; ?>" alt="IUM">
                </div>
                <div class="institution">Instituto Universitario Morelia</div>
            </div>
            <div class="header-right">
                <div class="doc-title">RECIBO DE INGRESO</div>
                <div class="doc-subtitle"><?php echo $categoria; ?></div>
                <div class="folio">Folio: <?php echo $folioEsc; ?></div>
                <div style="font-size: 10px; color: #666; margin-top: 4px;">Fecha: <?php echo $fecha; ?></div>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Destacado ciclo escolar -->
        <div class="highlight-box">
            <div class="highlight-title">PERIODO ACADÉMICO</div>
            <div class="highlight-value"><?php echo $anio ?: date('Y'); ?> | <?php echo $modalidad ?: 'N/A'; ?></div>
        </div>
        
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell half">
                    <span class="label">Alumno</span>
                    <div class="value"><?php echo $alumno ?: '-'; ?></div>
                </div>
                <div class="grid-cell half">
                    <span class="label">Matrícula</span>
                    <div class="value"><?php echo $matricula ?: '-'; ?></div>
                </div>
            </div>
        </div>
        
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell half">
                    <span class="label">Nivel</span>
                    <div class="value"><?php echo $nivel ?: '-'; ?></div>
                </div>
                <div class="grid-cell half">
                    <span class="label">Grado</span>
                    <div class="value"><?php echo $grado ?: '-'; ?></div>
                </div>
            </div>
        </div>
        
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell full">
                    <span class="label">Programa</span>
                    <div class="value"><?php echo $programa ?: '-'; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Monto -->
        <div class="monto-section">
            <div class="monto-label">MONTO TOTAL</div>
            <div class="monto-value"><?php echo $montoFormateado; ?></div>
            <div class="monto-currency">PESOS MEXICANOS (MXN)</div>
        </div>
        
        <!-- Cantidad con letra -->
        <div class="letra-box">
            <span class="label">Cantidad con letra</span>
            <div class="letra-text"><?php echo $cantidadConLetra ?: '-'; ?></div>
        </div>
        
        <!-- Método de Pago -->
        <div class="payment-box">
            <span class="label">Método de Pago</span>
            <?php if (!empty($pagosParciales)): ?>
                <div style="font-weight: bold; color: #9e1b32; margin-bottom: 4px;">Pago Dividido</div>
                <?php echo $detalleMetodos; ?>
            <?php else: ?>
                <div style="font-size: 11px; font-weight: bold;"><?php echo $metodo ?: '-'; ?></div>
            <?php endif; ?>
        </div>
        
        <!-- Observaciones -->
        <?php if ($observaciones): ?>
        <div>
            <span class="label">Observaciones</span>
            <div class="description-box"><?php echo nl2br($observaciones); ?></div>
        </div>
        <?php endif; ?>
        
        <!-- Firma -->
        <div class="signature-section">
            <div class="logo-box" style="margin-bottom: 8px;">
                <span style="color: white; font-weight: bold;">IUM</span>
            </div>
            <div class="signature-line"></div>
            <div class="signature-label">FIRMA DE QUIEN RECIBIÓ</div>
            <div class="signature-name">Ing. Ricardo Valdés Morales</div>
        </div>
        
        <div class="footer">
            Este documento es un comprobante interno de <?php echo strtolower($categoria); ?> del Instituto Universitario Morelia.
            <?php if ($reimpresion): ?>
                <strong style="color: #dc3545;"> | REIMPRESIÓN</strong>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="no-print" style="text-align: center; margin: 20px;">
        <button onclick="window.print()" style="background: #2b7be4; color: white; border: none; padding: 10px 24px; border-radius: 6px; cursor: pointer; font-weight: bold;">
            Imprimir Recibo
        </button>
    </div>
</body>
</html>
