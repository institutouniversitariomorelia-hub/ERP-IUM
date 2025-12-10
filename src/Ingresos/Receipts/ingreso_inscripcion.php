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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recibo <?php echo $categoria; ?> #<?php echo $folioEsc; ?></title>
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
    font-size: 8.2px;     /* +15% */
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
.grid { display: table; width: 100%; margin-bottom: 4px; }
.grid-row { display: table-row; }
.grid-cell { display: table-cell; padding: 2px 4px 2px 0; vertical-align: top; }

.label {
    font-size: 7.5px;   /* +15% */
    color: #444;
    font-weight: bold;
}

.value {
    font-size: 9.5px;   /* +15% */
    border-bottom: 1px solid #ccc;
    padding: 1px 0;
    min-height: 10px;   /* -30% */
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
    font-size: 18px;    /* -10% para que quepa */
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
    font-size: 8px;
}

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
    margin-top: 4px;   /* SUPER COMPACTO */
    text-align: center;
}

.signature-line {
    border-top: 1px solid #444;
    width: 55%;
    margin: 0 auto 3px auto;
}

.signature-label {
    font-size: 9px; /* +15% */
    font-weight: bold;
}

.signature-name {
    font-size: 10.5px; /* +15% */
    font-weight: bold;
    margin-top: 2px;
}

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
    