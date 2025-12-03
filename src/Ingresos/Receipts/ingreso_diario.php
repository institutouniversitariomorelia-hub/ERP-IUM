<?php
/**
 * Recibo de Ingreso - Registro Diario
 * Para: Colegiaturas, Constancias, Historiales, Certificados, Equivalencias, Credenciales, Otros
 */

require_once __DIR__ . '/../../../config/database.php';

// Obtener y validar folio
$folio = isset($_GET['folio']) ? (int)$_GET['folio'] : 0;
$reimpresion = isset($_GET['reimpresion']) ? (int)$_GET['reimpresion'] : 0;

if ($folio <= 0) {
    http_response_code(400);
    echo "Folio inválido.";
    exit;
}

// Consulta segura con JOIN para obtener nombre de categoría y pagos parciales
$sql = "SELECT i.*, c.nombre_categoria
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
    echo "Recibo no encontrado para folio: " . htmlspecialchars($folio);
    exit;
}

// Obtener pagos parciales si existen
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

// Formatear monto
$monto = isset($ingreso['monto']) ? (float)$ingreso['monto'] : 0.0;
$montoFormateado = '$ ' . number_format($monto, 2);
if (class_exists('NumberFormatter')) {
    try {
        $fmt = new NumberFormatter('es_MX', NumberFormatter::CURRENCY);
        $montoFormateado = $fmt->formatCurrency($monto, 'MXN');
    } catch (Exception $e) { /* fallback */ }
}

// Cantidad en letra
$cantidadConLetra = '';
if (class_exists('NumberFormatter')) {
    try {
        $entero = floor($monto);
        $centavos = round(($monto - $entero) * 100);
        $fmtSpell = new NumberFormatter('es_MX', NumberFormatter::SPELLOUT);
        $letras = $fmtSpell->format($entero);
        $letras = strtoupper($letras);
        $cantidadConLetra = $letras . ' PESOS ' . sprintf('%02d', $centavos) . '/100 M.N.';
    } catch (Exception $e) { $cantidadConLetra = ''; }
}

// Campos para el recibo
$logoPath = '../../../public/logo ium blanco.png';
$fecha = htmlspecialchars($ingreso['fecha'] ?? '');
$folioEsc = htmlspecialchars($ingreso['folio_ingreso'] ?? '');
$alumno = htmlspecialchars($ingreso['alumno'] ?? '');
$matricula = htmlspecialchars($ingreso['matricula'] ?? '');
$nivel = htmlspecialchars($ingreso['nivel'] ?? '');
$programa = htmlspecialchars($ingreso['programa'] ?? '');
$grado = htmlspecialchars($ingreso['grado'] ?? '');
$grupo = htmlspecialchars($ingreso['grupo'] ?? '');
$modalidad = htmlspecialchars($ingreso['modalidad'] ?? '');
$categoria = htmlspecialchars($ingreso['nombre_categoria'] ?? '');
$mes = htmlspecialchars($ingreso['mes_correspondiente'] ?? '');
$anio = htmlspecialchars($ingreso['anio'] ?? '');
$metodo = htmlspecialchars($ingreso['metodo_de_pago'] ?? $ingreso['metodo'] ?? '');
$observaciones = htmlspecialchars($ingreso['observaciones'] ?? '');

// Construir detalle de métodos de pago
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
    <title>Recibo de Ingreso #<?php echo $folioEsc; ?></title>
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
        
        .monto-section { text-align: right; padding: 6px 0; margin: 6px 0; }
        .monto-label { font-size: 8px; color: #666; font-weight: bold; }
        .monto-value { font-size: 20px; font-weight: bold; color: #9e1b32; line-height: 1.2; }
        .monto-currency { font-size: 8px; color: #666; }
        
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
        <!-- Header -->
        <div class="header">
            <div class="header-left">
                <div class="logo-box">
                    <img src="<?php echo $logoPath; ?>" alt="IUM">
                </div>
                <div class="institution">Instituto Universitario Morelia</div>
            </div>
            <div class="header-right">
                <div class="doc-title">RECIBO DE INGRESO</div>
                <div class="folio">Folio: <?php echo $folioEsc; ?></div>
                <div style="font-size: 10px; color: #666; margin-top: 4px;">Fecha: <?php echo $fecha; ?></div>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <!-- Información Principal -->
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell half">
                    <span class="label">Recibido de</span>
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
                    <span class="label">Programa</span>
                    <div class="value"><?php echo $programa ?: '-'; ?></div>
                </div>
            </div>
        </div>
        
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell" style="width: 25%;">
                    <span class="label">Grado</span>
                    <div class="value"><?php echo $grado ?: '-'; ?></div>
                </div>
                <div class="grid-cell" style="width: 25%;">
                    <span class="label">Grupo</span>
                    <div class="value"><?php echo $grupo ?: '-'; ?></div>
                </div>
                <div class="grid-cell" style="width: 50%;">
                    <span class="label">Modalidad</span>
                    <div class="value"><?php echo $modalidad ?: '-'; ?></div>
                </div>
            </div>
        </div>
        
        <div class="grid">
            <?php if (!empty($categoria)): ?>
            <div class="grid-row">
                <div class="grid-cell full">
                    <span class="label">Categoría</span>
                    <div class="value"><?php echo $categoria; ?></div>
                </div>
            </div>
            <?php endif; ?>
        
        <!-- Monto y Método de Pago -->
        <div style="display: table; width: 100%; margin: 10px 0;">
            <div style="display: table-cell; width: 50%; padding-right: 10px;">
                <div class="label">Cantidad con letra</div>
                <div class="value" style="font-size: 10px; font-style: italic; min-height: 30px; line-height: 1.4;">
                    <?php echo $cantidadConLetra ?: '-'; ?>
                </div>
            </div>
            <div style="display: table-cell; width: 50%; text-align: right;">
                <div class="monto-section">
                    <div class="monto-label">Monto Total</div>
                    <div class="monto-value"><?php echo $montoFormateado; ?></div>
                    <div class="monto-currency">MXN</div>
                </div>
            </div>
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
        
        <!-- Mes y Año -->
        <?php if ($mes || $anio): ?>
        <div class="grid">
            <div class="grid-row">
                <div class="grid-cell half">
                    <span class="label">Mes Correspondiente</span>
                    <div class="value"><?php echo $mes ?: '-'; ?></div>
                </div>
                <div class="grid-cell half">
                    <span class="label">Año</span>
                    <div class="value"><?php echo $anio ?: '-'; ?></div>
                </div>
            </div>
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
        
        <!-- Footer -->
        <div class="footer">
            Este documento es un comprobante interno de ingreso del Instituto Universitario Morelia.
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
