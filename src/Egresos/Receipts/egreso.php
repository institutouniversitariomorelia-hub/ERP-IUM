<?php
// recibo_egreso.php
// Archivo independiente para generar el recibo de egreso

// 1. Incluir conexión a BD
require_once __DIR__ . '/../../../config/database.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Seguridad simple: verificar si hay sesión
if (!isset($_SESSION['user_id'])) {
    die("Acceso denegado. Por favor, inicie sesión.");
}

// 2. Obtener y validar folio
$folio = isset($_GET['folio']) ? (int)$_GET['folio'] : 0;
$reimpresion = isset($_GET['reimpresion']) ? (int)$_GET['reimpresion'] : 0;

if ($folio <= 0) {
    http_response_code(400);
    echo "Folio inválido.";
    exit;
}

// 3. Consulta segura (adaptada para egresos)
$sql = "SELECT e.*, c.nombre AS nombre_categoria 
        FROM egresos e
        LEFT JOIN categorias c ON e.id_categoria = c.id_categoria 
        WHERE e.folio_egreso = ? LIMIT 1";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo "Error en la consulta: " . htmlspecialchars($conn->error);
    exit;
}
$stmt->bind_param("i", $folio);
$stmt->execute();
$res = $stmt->get_result();
$egreso = $res->fetch_assoc();
$stmt->close();
$conn->close(); // Cerramos la conexión

if (!$egreso) {
    echo "Recibo no encontrado para folio: " . htmlspecialchars($folio);
    exit;
}

// 4. Formatear Monto (copiado de tu ejemplo de ingreso)
$monto = isset($egreso['monto']) ? (float)$egreso['monto'] : 0.0;
$montoFormateado = '$ ' . number_format($monto, 2);
if (class_exists('NumberFormatter')) {
    try {
        $fmt = new NumberFormatter('es_MX', NumberFormatter::CURRENCY);
        $montoFormateado = $fmt->formatCurrency($monto, 'MXN');
    } catch (Exception $e) { /* fallback */ }
}

// 5. Cantidad en letra (copiado de tu ejemplo de ingreso)
$cantidadConLetra = '';
if (class_exists('NumberFormatter')) {
    try {
        $entero = floor($monto);
        $centavos = round(($monto - $entero) * 100);
        $fmtSpell = new NumberFormatter('es_MX', NumberFormatter::SPELLOUT);
        $letras = $fmtSpell->format($entero);
        $letras = strtoupper($letras);
        $cantidadConLetra = '(' . $letras . ' PESOS ' . sprintf('%02d', $centavos) . '/100 M.N.)';
    } catch (Exception $e) { $cantidadConLetra = '(No disponible)'; }
}

// 6. Preparar variables para el HTML (campos de Opción 3)
$logoPath = '../../../public/logo ium blanco.png'; // Ruta desde src/Egresos/Receipts/
$fecha = htmlspecialchars($egreso['fecha'] ?? 'N/A');
// Formatear fecha (si tienes 'intl' instalado)
if (class_exists('IntlDateFormatter')) {
     try {
        $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
        $fecha = $formatter->format(new DateTime($fecha));
     } catch (Throwable $e) { /* fallback a la fecha simple */ }
}

$folioEsc = htmlspecialchars($egreso['folio_egreso'] ?? '');
$nombreCategoria = htmlspecialchars($egreso['nombre_categoria'] ?? 'Sin categoría');
$proveedor = htmlspecialchars($egreso['proveedor'] ?? '-');
$metodo = htmlspecialchars($egreso['forma_pago'] ?? '-');
$descripcion = htmlspecialchars($egreso['descripcion'] ?? '-');
$destinatario = htmlspecialchars($egreso['destinatario'] ?? '-'); // Para la firma

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de Egreso #<?php echo $folioEsc; ?></title>
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
        <!-- Encabezado -->
        <div class="header">
            <div class="header-left">
                <div class="logo-box">
                    <img src="<?php echo $logoPath; ?>" alt="IUM">
                </div>
                <div class="institution">Instituto Universitario Morelia</div>
            </div>
            <div class="header-right">
                <div class="doc-title">COMPROBANTE DE EGRESO</div>
                <div class="folio">Folio: <?php echo $folioEsc; ?></div>
                <div style="font-size: 8px; color: #666; margin-top: 2px;">Fecha: <?php echo $fecha; ?></div>
            </div>
        </div>
        
        <div class="divider"></div>
        
        <div class="content">
            <!-- Información del Egreso -->
            <div class="grid">
                <div class="grid-row">
                    <div class="grid-cell half">
                        <span class="label">Proveedor</span>
                        <div class="value"><?php echo $proveedor; ?></div>
                    </div>
                    <div class="grid-cell half">
                        <span class="label">Método de Pago</span>
                        <div class="value"><?php echo $metodo; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="grid">
                <div class="grid-row">
                    <div class="grid-cell full">
                        <span class="label">Categoría</span>
                        <div class="value"><?php echo $nombreCategoria; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Monto -->
            <div class="monto-section">
                <div class="monto-label">MONTO TOTAL</div>
                <div class="monto-value"><?php echo $montoFormateado; ?></div>
                <div class="monto-currency">PESOS MEXICANOS (MXN)</div>
            </div>
            
            <!-- Cantidad con Letra -->
            <div class="letra-box">
                <div class="letra-text">CANTIDAD CON LETRA<br><?php echo $cantidadConLetra; ?></div>
            </div>
            
            <!-- Descripción -->
            <div class="grid">
                <div class="grid-row">
                    <div class="grid-cell full">
                        <span class="label">Descripción</span>
                        <div class="description-box"><?php echo nl2br($descripcion); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Firma -->
        <div class="signature-section">
            <div class="signature-line"></div>
            <div class="signature-label">FIRMA DE QUIEN RECIBIÓ</div>
            <div class="signature-name"><?php echo $destinatario; ?></div>
        </div>
        
        <div class="footer">
            Este documento es un comprobante interno de egreso del Instituto Universitario Morelia.
        </div>
    </div>
</body>
</html>