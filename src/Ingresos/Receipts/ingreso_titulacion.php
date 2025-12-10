<?php
/**
 * Recibo de Ingreso - Titulación
 * Para: Trámites de titulación
 */

require_once __DIR__ . '/../../../config/database.php';

$folio = isset($_GET['folio']) ? (int)$_GET['folio'] : 0;
$reimpresion = isset($_GET['reimpresion']) ? (int)$_GET['reimpresion'] : 0;

if ($folio <= 0) {
    http_response_code(400);
    echo "Folio inválido.";
    exit;
}

$sql = "SELECT i.*, c.nombre AS nombre_categoria, c.concepto 
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

// Pagos parciales
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

// Formateo
$monto = (float)($ingreso['monto'] ?? 0);
$montoFormateado = '$' . number_format($monto, 2);
if (class_exists('NumberFormatter')) {
    try {
        $fmt = new NumberFormatter('es_MX', NumberFormatter::CURRENCY);
        $montoFormateado = $fmt->formatCurrency($monto, 'MXN');
    } catch (Exception $e) {}
}

$cantidadConLetra = '';
function numToWordsEs($num) {
    $num = (int)$num;
    $U = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho', 'nueve', 'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete', 'dieciocho', 'diecinueve'];
    $T = ['', '', 'veinte', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta', 'ochenta', 'noventa'];
    $C = ['', 'cien', 'doscientos', 'trescientos', 'cuatrocientos', 'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];
    $to99 = function($n) use ($U, $T) {
        if ($n < 20) return $U[$n];
        if ($n == 20) return 'veinte';
        $d = intdiv($n, 10); $u = $n % 10;
        if ($d == 2 && $u > 0) return 'veinti' . $U[$u];
        return $T[$d] . ($u ? ' y ' . $U[$u] : '');
    };
    $to999 = function($n) use ($C, $to99) {
        if ($n == 0) return '';
        if ($n == 100) return 'cien';
        $c = intdiv($n, 100); $r = $n % 100;
        $pref = $c ? (($c == 1) ? 'ciento' : $C[$c]) : '';
        return trim($pref . ($r ? ' ' . $to99($r) : ''));
    };
    if ($num == 0) return 'cero';
    $millones = intdiv($num, 1000000); $resto = $num % 1000000;
    $miles = intdiv($resto, 1000); $unidades = $resto % 1000;
    $parts = [];
    if ($millones) $parts[] = ($millones == 1 ? 'un millón' : trim(numToWordsEs($millones) . ' millones'));
    if ($miles) $parts[] = ($miles == 1 ? 'mil' : trim($to999($miles) . ' mil'));
    if ($unidades) $parts[] = $to999($unidades);
    return trim(implode(' ', $parts));
}
if (class_exists('NumberFormatter')) {
    try {
        $entero = floor($monto);
        $centavos = round(($monto - $entero) * 100);
        $fmtSpell = new NumberFormatter('es_MX', NumberFormatter::SPELLOUT);
        $letras = strtoupper($fmtSpell->format($entero));
        $cantidadConLetra = $letras . ' PESOS ' . sprintf('%02d', $centavos) . '/100 M.N.';
    } catch (Exception $e) { /* fallback abajo */ }
}
if ($cantidadConLetra === '') {
    $entero = floor($monto);
    $centavos = round(($monto - $entero) * 100);
    $letras = strtoupper(numToWordsEs($entero));
    $cantidadConLetra = $letras . ' PESOS ' . sprintf('%02d', $centavos) . '/100 M.N.';
}

$logoPath = '../../../public/logo ium rojo (3).png';
$fecha = htmlspecialchars($ingreso['fecha'] ?? '');
$folioEsc = htmlspecialchars($ingreso['folio_ingreso'] ?? '');
$alumno = htmlspecialchars($ingreso['alumno'] ?? '');
$matricula = htmlspecialchars($ingreso['matricula'] ?? '');
$nivel = htmlspecialchars($ingreso['nivel'] ?? '');
$programa = htmlspecialchars($ingreso['programa'] ?? '');
$categoria = htmlspecialchars($ingreso['nombre_categoria'] ?? '');
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
    <title>Recibo de Titulación #<?php echo $folioEsc; ?></title>
    <style>/* ===========================================
   AJUSTE EXACTO PARA MEDIA CARTA 13.7 CM ALTO
   AUMENTO DE LETRA (sin mover contenedores)
   =========================================== */

@page {
    size: Letter portrait !important;
    margin: 0 !important;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Aumentamos letra global */
body {
    font-family: Arial, sans-serif;
    font-size: 9.5px;   /* antes 8.2px */
    line-height: 1.20;
    background: #f2f2f2;
    padding: 0;
}

/* Mantiene dimensiones */
.page {
    width: 100%;
    max-width: 8.5in;
    height: 13.4cm;
    padding: 0.2in 0.25in;
    background: white;
    border-radius: 4px;
    overflow: hidden;
}

/* -------- ENCABEZADO -------- */
.logo-box img {
    height: 30px; /* antes 26px */
}
.institution {
    font-size: 9.5px; /* antes 8px */
    font-weight: bold;
}

.doc-title {
    font-size: 14px; /* antes 12px */
    font-weight: bold;
}

.folio {
    font-size: 13px; /* antes 11px */
    font-weight: bold;
    color: #9e1b32;
}

/* -------- TABLAS -------- */
.label {
    font-size: 9px;    /* antes 7.5px */
    color: #444;
    font-weight: bold;
}

.value {
    font-size: 11.5px; /* antes 9.5px */
    border-bottom: 1px solid #ccc;
    padding: 1px 0;
    min-height: 11px;
}

/* --------- MONTO --------- */
.monto-label {
    font-size: 9.5px; /* antes 8px */
}

.monto-value {
    font-size: 22px; /* antes 18px */
    font-weight: bold;
    color: #9e1b32;
}

.monto-currency {
    font-size: 9px; /* antes 8px */
}

/* --------- METODO DE PAGO --------- */
.payment-box {
    background: #f8f8f8;
    border: 1px solid #ccc;
    padding: 4px;
    border-radius: 3px;
    font-size: 10.5px; /* antes 8px */
}

/* -------- OBSERVACIONES -------- */
.description-box {
    font-size: 10px; /* antes 8px */
    min-height: 35px;
}

/* -------- FIRMA -------- */
.signature-label {
    font-size: 11px; /* antes 9px */
    font-weight: bold;
}

.signature-name {
    font-size: 13px; /* antes 10.5px */
    font-weight: bold;
}

/* -------- FOOTER -------- */
.footer {
    font-size: 9px; /* antes 7.5px */
    margin-top: 4px;
    text-align: center;
    border-top: 1px solid #ddd;
    padding-top: 3px;
}

/* -------- IMPRESIÓN -------- */
@media print {
    body {
        background: white;
    }
    .no-print {
        display: none !important;
    }
    .page {
        border: none;
        box-shadow: none;
    }
}
</style>
</head>
<body>
    <div class="no-print"><button class="print-btn" onclick="window.print()">Imprimir</button></div>
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
                <div class="doc-subtitle">Trámite de Titulación</div>
                <div class="folio">Folio: <?php echo $folioEsc; ?></div>
                <div style="font-size: 10px; color: #666; margin-top: 4px;">Fecha: <?php echo $fecha; ?></div>
            </div>
        </div>
        
        <div class="divider"></div>
        
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
                <div class="grid-cell full">
                    <span class="label">Concepto</span>
                    <div class="value"><?php echo $categoria ?: 'TITULACIÓN'; ?></div>
                </div>
            </div>
        </div>
        
        <!-- Monto destacado -->
        <div class="monto-section">
            <div class="monto-label">MONTO TOTAL DEL TRÁMITE</div>
            <div class="monto-value"><?php echo $montoFormateado; ?></div>
            <div class="monto-currency">PESOS MEXICANOS (MXN)</div>
        </div>
        
        <!-- Cantidad en letra (solo texto) -->
        <div class="letra-box">
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
            Este documento es un comprobante interno de ingreso por trámite de titulación del Instituto Universitario Morelia.
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
