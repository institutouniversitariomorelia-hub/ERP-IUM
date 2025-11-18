<?php

require_once __DIR__ . '/db.php';

// Obtener y validar folio
$folio = isset($_GET['folio']) ? (int)$_GET['folio'] : 0;
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

// Cantidad en letra (ej: SETECIENTOS PESOS 00/100 M.N.)
$cantidadConLetra = '';
if (class_exists('NumberFormatter')) {
    try {
        $entero = floor($monto);
        $centavos = round(($monto - $entero) * 100);
        $fmtSpell = new NumberFormatter('es_MX', NumberFormatter::SPELLOUT);
        $letras = $fmtSpell->format($entero);
        $letras = strtoupper($letras);
        $cantidadConLetra = '(' . $letras . ' PESOS ' . sprintf('%02d', $centavos) . '/100 M.N.)';
    } catch (Exception $e) { $cantidadConLetra = ''; }
}

// Campos para el recibo
$logoPath = 'public/logo ium blanco.png';
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
$concepto = htmlspecialchars($ingreso['concepto'] ?? '');
$mes = htmlspecialchars($ingreso['mes_correspondiente'] ?? '');
$anio = htmlspecialchars($ingreso['anio'] ?? '');
$metodo = htmlspecialchars($ingreso['metodo_de_pago'] ?? $ingreso['metodo'] ?? '');
$observaciones = htmlspecialchars($ingreso['observaciones'] ?? '');

// Construir detalle de métodos de pago
$detalleMetodos = '';
if (!empty($pagosParciales)) {
    $detalleMetodos = '<div style="margin-top: 8px;">';
    foreach ($pagosParciales as $pago) {
        $metodoPago = htmlspecialchars($pago['metodo_pago']);
        $montoPago = number_format((float)$pago['monto'], 2);
        $detalleMetodos .= '<div style="padding: 4px 0; font-size: 13px;"><strong>' . $metodoPago . ':</strong> $' . $montoPago . '</div>';
    }
    $detalleMetodos .= '</div>';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de Ingreso #<?php echo $folioEsc; ?></title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background:#f3f5f8; margin:20px; color:#222; }
        .card { max-width:820px; margin:0 auto; background:#fff; border-radius:8px; box-shadow:0 6px 18px rgba(15,23,42,0.08); padding:28px; }
        .top { display:flex; justify-content:space-between; align-items:flex-start; }
        .logo { background:#8b1d2b; color:#fff; padding:10px 18px; border-radius:4px; font-weight:700; }
        .title { text-align:right; flex:1; margin-left:20px; }
        .title h1 { margin:0; font-size:22px; letter-spacing:0.4px; color:#123; }
        .folio { color:#b12b3a; font-weight:700; }
        .hr { height:4px; background:#8b1d2b; margin:16px 0 22px 0; border-radius:2px; }

        .grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
        .left-col, .right-col { padding:6px 0; }
        .label { font-size:12px; color:#666; font-weight:700; }
        .value { font-size:14px; color:#222; margin-top:6px; }

        .monto-wrap { text-align:right; }
        .monto { font-size:26px; color:#b12b3a; font-weight:800; }
        .cantidad-letra { font-style:italic; color:#666; margin-top:8px; }

        .descripcion { margin-top:16px; border:1px solid #e6e9ee; padding:14px; border-radius:6px; background:#fbfdff; color:#333; }
        .descripcion p { margin:0; }

        .signature-area { margin-top:48px; display:flex; justify-content:space-between; align-items:center; }
        .sig { width:360px; text-align:center; }
        .sig .btn-i { display:inline-block; background:#8b1d2b; color:#fff; padding:8px 24px; border-radius:6px; font-weight:700; margin-bottom:18px; }
        .sig .line { border-top:1px solid #ddd; padding-top:8px; color:#333; font-weight:700; }

        .disclaimer { font-size:12px; color:#8b8f99; margin-top:10px; }

        .print-btn { text-align:center; margin-top:22px; }
        .print-btn button { background:#2b7be4; color:#fff; border:none; padding:10px 22px; border-radius:8px; cursor:pointer; font-weight:700; }

        @media (max-width: 768px) {
            body { margin: 10px; }
            .card { padding: 15px; }
            .top { flex-direction: column; }
            .title { margin-left: 0; margin-top: 15px; text-align: left; }
            .title h1 { font-size: 18px; }
            .grid { grid-template-columns: 1fr; gap: 10px; }
            .monto { font-size: 20px; }
            .signature-area { flex-direction: column; gap: 20px; }
            .sig { width: 100%; }
            .cantidad-letra { font-size: 12px; }
        }

        @media print { body { background:#fff; } .print-btn { display:none; } .card { box-shadow:none; border-radius:0; padding:0; } }
    </style>
</head>
<body>
    <div class="card">
        <div class="top">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="width:120px;">
                    <img src="<?php echo $logoPath; ?>" alt="Logo" style="max-width:100%; display:block;">
                </div>
                <div style="font-size:13px; color:#555;">Instituto Universitario Morelia</div>
            </div>

            <div class="title">
                <div style="display:flex; justify-content:flex-end; align-items:center; gap:18px;">
                    <div style="text-align:right;">
                        <div style="font-size:12px; color:#666;">Folio:</div>
                        <div class="folio">#<?php echo $folioEsc; ?></div>
                    </div>
                </div>
                <h1>COMPROBANTE DE INGRESO</h1>
            </div>
        </div>

        <div class="hr"></div>

        <div class="grid">
            <div class="left-col">
                <div class="label">FECHA DE EMISIÓN</div>
                <div class="value"><?php echo $fecha; ?></div>

                <div style="height:10px;"></div>

                <div class="label">RECIBIDO DE</div>
                <div class="value"><?php echo $alumno ?: '-'; ?></div>

                <div style="height:10px;"></div>

                <div class="label">CANTIDAD CON LETRA</div>
                <div class="value cantidad-letra"><?php echo $cantidadConLetra ?: '(No disponible)'; ?></div>
            </div>

            <div class="right-col">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div class="label">MONTO TOTAL</div>
                    </div>
                    <div class="monto-wrap">
                        <div class="monto"><?php echo $montoFormateado; ?></div>
                        <div style="font-size:12px; color:#888;">MXN</div>
                    </div>
                </div>

                <div style="height:18px;"></div>

                <div class="label">MÉTODO DE PAGO</div>
                <div class="value">
                    <?php 
                    if (!empty($pagosParciales)) {
                        echo '<span style="color: #b12b3a; font-weight: 600;">Pago Dividido</span>';
                        echo $detalleMetodos;
                    } else {
                        echo $metodo ?: '-';
                    }
                    ?>
                </div>

                <div style="height:8px;"></div>

            </div>
        </div>

        <div class="descripcion">
            <div class="label">DESCRIPCIÓN</div>
            <p><?php echo $observaciones ?: $concepto ?: '-'; ?></p>
        </div>

        <div class="signature-area">
            <div style="width:45%;">
                <div class="disclaimer">Este documento es un comprobante interno de ingreso del Instituto Universitario Morelia.</div>
            </div>
            <div class="sig">
                <div class="btn-i">IUM</div>
                <div class="line">FIRMA DE QUIEN RECIBIÓ</div>
                <div style="margin-top:8px; font-weight:700;">Ing. Ricardo Valdés</div>
            </div>
        </div>

        <div class="print-btn">
            <button onclick="window.print()">Imprimir Recibo</button>
        </div>
    </div>
</body>
</html>
