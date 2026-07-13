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

// Cantidad en letra con fallback
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

// Campos para el recibo
$logoPath = '../../../public/logo ium rojo (3).png';
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

$detalleMetodos = '';
if (!empty($pagosParciales)) {
    $methods = [];
    $amounts = [];
    foreach ($pagosParciales as $pago) {
        $methods[] = '<div class="pm-item">' . htmlspecialchars($pago['metodo_pago']) . '</div>';
        $amounts[] = '<span class="pa-item">$' . number_format((float)$pago['monto'], 2) . '</span>';
    }
    $detalleMetodos = '<div class="payment-grid">'
        . '<div class="payment-methods">' . implode('', $methods) . '</div>'
        . '<div class="payment-amounts">' . implode('', $amounts) . '</div>'
        . '</div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo de Ingreso #<?php echo $folioEsc; ?></title>

<style>
/* ===========================================
   AJUSTE EXACTO PARA MEDIA CARTA 13.7 CM ALTO
   =========================================== */

/* Fuerza impresión vertical sin márgenes */
@page {
    size: Letter portrait !important;
    margin: 0 !important;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

/* Reducimos altura total + aumentamos letra */
body {
    font-family: Arial, sans-serif;
    font-size: 10.5px;     /* +15% */
    line-height: 1.15;
    background: #f2f2f2;
    padding: 0;
}

/* Contenedor total reducido -25% */
.page {
    width: 100%;
    max-width: 8.5in;
    height: 13.4cm;        /* AJUSTE CRÍTICO */
    padding: 0.2in 0.25in;
    background: white;
    border-radius: 4px;
    overflow: hidden;      /* evita cortar */
    display: flex;
    flex-direction: column;
}

/* ------------------------ */
/*   ENCABEZADO COMPACTO    */
/* ------------------------ */
.header { display: table; width: 100%; margin-bottom: 4px; }
.header-left { display: table-cell; width: 35%; vertical-align: top; }
.header-right { display: table-cell; width: 65%; vertical-align: top; text-align: right; }

.logo-box {
    display: inline-block;
    background: #9e1b32;
    padding: 3px 6px;
    border-radius: 3px;
}
.logo-box img {
    height: 26px;  /* -15% */
}

.institution { font-size: 8px; font-weight: bold; }

.doc-title { font-size: 12px; font-weight: bold; }
.folio { font-size: 11px; font-weight: bold; color: #9e1b32; }

.divider { height: 2px; background: #9e1b32; margin: 4px 0; }

/* ------------------------ */
/*   TABLA DE DATOS COMPACTA */
/* ------------------------ */
.grid { display: table; width: 100%; margin-bottom: 2px; }
.grid-row { display: table-row; }
.grid-cell { display: table-cell; padding: 2px 4px 2px 0; vertical-align: top; }

.label {
    font-size: 10px;   /* +15% */
    color: #444;
    font-weight: bold;
}

.value {
    font-size: 11px;   /* +15% */
    border-bottom: 1px solid #ccc;
    padding: 0px 0;
    min-height: 9px;   /* -30% */
}

/* ------------------------ */
/*      MONTO REDUCIDO      */
/* ------------------------ */
.monto-section {
    text-align: right;
    margin: 4px 0;
}
.monto-label { font-size: 8px; }
.monto-value {
    font-size: 16px;    /* -10% para que quepa */
    font-weight: bold;
    color: #9e1b32;
}
.monto-currency { font-size: 8px; }

/* ------------------------ */
/*   METODO DE PAGO         */
/* ------------------------ */
.payment-box {
    background: #f8f8f8;
    border: 1px solid #ccc;
    padding: 4px;
    border-radius: 3px;
    font-size: 12px;
    margin-bottom: 4px;
}

/* Payments: methods in a single top row, amounts in a single row beneath them, left-aligned */
.payment-grid { display:flex; flex-direction:column; gap:6px; align-items:flex-start; }
.payment-methods { display:flex; flex-direction:row; gap:12px; flex-wrap:nowrap; }
.payment-amounts { display:flex; flex-direction:row; gap:12px; align-items:center; white-space:nowrap; }
.pm-item { font-weight:600; color:#333; font-size:12px; display:inline-block; }
.pa-item { font-weight:700; color:#9e1b32; font-size:12px; display:inline-block; }

/* ------------------------ */
/*   OBSERVACIONES          */
/* ------------------------ */
.description-box {
    border: 1px solid #ddd;
    padding: 6px;
    min-height: 35px; /* -25% */
    background: #fafafa;
    border-radius: 3px;
    font-size: 8px;
}

/* ------------------------ */
/*     FIRMA (CRÍTICO)      */
/* ------------------------ */
.signature-section {
    margin-top: auto; /* push to bottom of .page */
    padding-top: 6px;
    text-align: center;
}

.signature-section .logo-box { display: inline-block; margin-bottom: 6px; }

.signature-line {
    border-top: 1px solid #444;
    width: 55%; /* línea centrada y visible */
    margin: 6px auto;
}

.signature-label {
    font-size: 9px;
    font-weight: bold;
    text-align: center;
}

.signature-name {
    font-size: 10.5px;
    font-weight: bold;
    margin-top: 2px;
    text-align: center;
}

/* Reduce vertical space between payment box and amount-in-words */
.letra-box { margin-top: 4px; margin-bottom: 4px; font-size: 10px; }

/* ------------------------ */
/*       FOOTER             */
/* ------------------------ */
.footer {
    font-size: 7.5px;
    margin-top: 4px;
    text-align: center;
    border-top: 1px solid #ddd;
    padding-top: 3px;
}

/* ------------------------ */
/*    IMPRESIÓN             */
/* ------------------------ */
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

/* Unified red print button */
.print-btn { background: #9e1b32; color: #fff; border: none; border-radius: 4px; padding: 8px 12px; font-size: 12px; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
.print-btn:hover { background: #b7213c; }
.print-container { position: fixed; left: 50%; bottom: 12px; transform: translateX(-50%); z-index: 9999; }
</style>

</head>
<body>

<!-- print button moved to bottom center -->

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

    <?php if (!empty($categoria)): ?>
    <div class="grid">
        <div class="grid-row">
            <div class="grid-cell full">
                <span class="label">Categoría</span>
                <div class="value"><?php echo $categoria; ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div style="display: table; width: 100%; margin: 6px 0;">
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

    <div class="payment-box">
        <span class="label">Método de Pago</span>
        <?php if (!empty($pagosParciales)): ?>
            <div style="font-weight: bold; color: #9e1b32; margin-bottom: 4px;">Pago Dividido</div>
            <?php echo $detalleMetodos; ?>
        <?php else: ?>
            <div style="font-size: 11px; font-weight: bold;"><?php echo $metodo ?: '-'; ?></div>
        <?php endif; ?>
    </div>

    <?php if ($observaciones): ?>
    <div>
        <span class="label">Observaciones</span>
        <div class="description-box"><?php echo nl2br($observaciones); ?></div>
    </div>
    <?php endif; ?>

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

    <div class="signature-section">
        <div class="logo-box" style="margin-bottom: 8px;">
            <span style="color: white; font-weight: bold;">IUM</span>
        </div>
        <div class="signature-line"></div>
        <div class="signature-label">FIRMA DE QUIEN RECIBIÓ</div>
        <div class="signature-name">Ing. Ricardo Valdés Morales</div>
    </div>

    <div class="footer">
        Este documento es un comprobante interno de ingreso del Instituto Universitario Morelia.
        <?php if ($reimpresion): ?>
            <strong style="color: #dc3545;"> | REIMPRESIÓN</strong>
        <?php endif; ?>
    </div>

    </div>

    <div class="no-print print-container">
        <button class="print-btn" onclick="window.print()">Imprimir</button>
    </div>

</body>
</html>