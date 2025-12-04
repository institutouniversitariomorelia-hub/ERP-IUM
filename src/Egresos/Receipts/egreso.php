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

// 5. Cantidad en letra: usar intl si existe; fallback simple si no
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
        $letras = $fmtSpell->format($entero);
        $letras = strtoupper($letras);
        $cantidadConLetra = '(' . $letras . ' PESOS ' . sprintf('%02d', $centavos) . '/100 M.N.)';
    } catch (Exception $e) { /* fallback abajo */ }
}
if ($cantidadConLetra === '') {
    $entero = floor($monto);
    $centavos = round(($monto - $entero) * 100);
    $letras = strtoupper(numToWordsEs($entero));
    $cantidadConLetra = '(' . $letras . ' PESOS ' . sprintf('%02d', $centavos) . '/100 M.N.)';
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
        /* Tamaño media carta (8.5 x 5.5 pulgadas) */
        @page { size: 8.5in 5.5in; margin: 0; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 8px; line-height: 1.25; background: #f2f2f2; display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; padding: 16px; }
        .page { width: 8.5in; height: 5.5in; padding: 0.2in 0.25in; position: relative; background: #fff; display: flex; flex-direction: column; border: 1px solid #e5e5e5; border-radius: 6px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); }

        .header { display: table; width: 100%; margin-bottom: 8px; }
        .header-left { display: table-cell; width: 35%; vertical-align: top; }
        .header-right { display: table-cell; width: 65%; vertical-align: top; text-align: right; }
        .logo-box { display: inline-block; background: #9e1b32; padding: 5px 9px; border-radius: 4px; }
        .logo-box img { height: 34px; vertical-align: middle; }
        .institution { font-size: 8px; color: #333; margin-top: 3px; font-weight: bold; letter-spacing: 0.2px; }
        .doc-title { font-size: 14px; font-weight: 800; color: #1a1a1a; margin-bottom: 2px; letter-spacing: 0.3px; }
        .folio { font-size: 11px; color: #9e1b32; font-weight: 700; }

        .divider { height: 2px; background: linear-gradient(90deg, #9e1b32, #c62828 60%, #9e1b32); margin: 6px 0 8px; border-radius: 2px; }

        .content { flex: 1; display: flex; flex-direction: column; }

        .grid { display: table; width: 100%; margin-bottom: 6px; }
        .grid-row { display: table-row; }
        .grid-cell { display: table-cell; padding: 4px 8px 4px 0; vertical-align: top; }
        .grid-cell.full { width: 100%; }
        .grid-cell.half { width: 50%; }

        .label { font-size: 8px; color: #666; font-weight: 700; text-transform: uppercase; display: block; margin-bottom: 2px; }
        .value { font-size: 10px; color: #000; border-bottom: 1px solid #e0e0e0; padding-bottom: 3px; min-height: 16px; }

        .monto-section { background: #f8f9fa; border: 2px solid #9e1b32; padding: 10px; text-align: center; margin: 10px 0; border-radius: 6px; }
        .monto-label { font-size: 9px; color: #666; font-weight: 700; margin-bottom: 4px; letter-spacing: 0.2px; }
        .monto-value { font-size: 22px; font-weight: 800; color: #9e1b32; line-height: 1.15; }
        .monto-currency { font-size: 9px; color: #666; margin-top: 3px; }

        .letra-box { background: #fffdf3; border: 1px solid #e6c565; padding: 8px; margin: 8px 0; border-radius: 4px; }
        .letra-text { font-size: 8px; font-style: italic; color: #333; line-height: 1.35; }

        .payment-box { background: #f5f5f5; border: 1px solid #ddd; padding: 7px; margin: 6px 0; border-radius: 4px; font-size: 8px; }
        .description-box { border: 1px solid #ddd; padding: 9px; min-height: 50px; background: #fafafa; margin: 8px 0; border-radius: 4px; font-size: 8px; flex: 1; }

        .signature-section { margin-top: auto; padding-top: 18px; text-align: center; }
        .signature-line { border-top: 1px solid #444; width: 58%; margin: 0 auto 6px auto; }
        .signature-label { font-size: 10px; font-weight: 700; color: #333; letter-spacing: 0.2px; }
        .signature-name { font-size: 11px; color: #000; margin-top: 3px; font-weight: 800; }

        .footer { font-size: 8px; color: #888; text-align: center; border-top: 1px solid #eee; padding-top: 5px; margin-top: 8px; }
        .watermark { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg); font-size: 72px; color: rgba(220, 53, 69, 0.12); font-weight: 800; z-index: 0; pointer-events: none; }

        .no-print { position: fixed; top: 16px; right: 16px; z-index: 10; }
        .print-btn { background: #9e1b32; color: #fff; border: none; border-radius: 4px; padding: 8px 12px; font-size: 12px; cursor: pointer; box-shadow: 0 2px 6px rgba(0,0,0,0.15); }
        .print-btn:hover { background: #b7213c; }
        @media print { body { margin: 0; background: none; display: block; } .no-print { display: none; } .page { box-shadow: none; border: none; } }
    </style>
</head>
<body>
    <div class="no-print"><button class="print-btn" onclick="window.print()">Imprimir</button></div>
    
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