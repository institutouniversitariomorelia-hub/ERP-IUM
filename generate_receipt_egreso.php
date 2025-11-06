<?php
// recibo_egreso.php
// Archivo independiente para generar el recibo de egreso

// 1. Incluir conexión a BD
require_once __DIR__ . '/db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Seguridad simple: verificar si hay sesión
if (!isset($_SESSION['user_id'])) {
    die("Acceso denegado. Por favor, inicie sesión.");
}

// 2. Obtener y validar folio
$folio = isset($_GET['folio']) ? (int)$_GET['folio'] : 0;
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
$logoPath = 'public/logo ium blanco.png'; // Ruta de tu layout
$fecha = htmlspecialchars($egreso['fecha'] ?? 'N/A');
// Formatear fecha (si tienes 'intl' instalado)
if (class_exists('IntlDateFormatter')) {
     try {
        $formatter = new IntlDateFormatter('es_ES', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
        $fecha = $formatter->format(new DateTime($fecha));
     } catch (Throwable $e) { /* fallback a la fecha simple */ }
}

$folioEsc = htmlspecialchars($egreso['folio_egreso'] ?? '');
$proveedor = htmlspecialchars($egreso['proveedor'] ?? '-');
$metodo = htmlspecialchars($egreso['forma_pago'] ?? '-');
$descripcion = htmlspecialchars($egreso['descripcion'] ?? '-');
$destinatario = htmlspecialchars($egreso['destinatario'] ?? '-'); // Para la firma

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprobante de Egreso #<?php echo $folioEsc; ?></title>
    <!-- Usamos Tailwind CSS como la Opción 3 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .ium-red { background-color: #9e1b32; }
        .ium-red-text { color: #9e1b32; }
        .ium-red-border { border-color: #9e1b32; }
        @media print {
            body { background-color: #fff; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none; }
            .recibo-wrapper { box-shadow: none; border: none; margin: 0; padding: 0; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans p-4 md:p-8">
    
    <div class="max-w-3xl mx-auto recibo-wrapper">

        <!-- Botón de Imprimir -->
        <div class="text-center md:text-right mb-4 no-print">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg shadow transition duration-200">
                Imprimir Recibo
            </button>
        </div>

        <!-- Contenido del Recibo (Opción 3) -->
        <div class="bg-white p-6 md:p-10 rounded-lg shadow-lg border border-gray-200">
            
            <!-- === Encabezado === -->
            <header class="flex justify-between items-center pb-4 border-b-4 ium-red-border">
                <div>
                    <!-- Logo desde la carpeta 'public' -->
                    <img src="<?php echo $logoPath; ?>" alt="Logo IUM" style="height: 56px; background-color: #9e1b32; padding: 4px; border-radius: 4px;">
                    <h1 class="text-gray-700 text-sm mt-1 font-semibold">Instituto Universitario Morelia</h1>
                </div>
                <div class="text-right">
                    <h2 class="text-2xl font-bold text-gray-800 uppercase">Comprobante de Egreso</h2>
                    <div class="text-lg font-semibold ium-red-text mt-1">
                        Folio: <span class="text-gray-900 font-mono">#<?php echo $folioEsc; ?></span>
                    </div>
                </div>
            </header>

            <!-- === Información en Columnas === -->
            <section class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                <div>
                    <span class="text-sm font-semibold text-gray-500 uppercase">Fecha de Emisión</span>
                    <p class="text-lg text-gray-900 border-b border-dashed border-gray-400 pb-1"><?php echo $fecha; ?></p>
                </div>
                <div>
                    <span class="text-sm font-semibold text-gray-500 uppercase">Monto Total</span>
                    <p class="text-2xl font-bold ium-red-text border-b border-dashed border-gray-400 pb-1"><?php echo $montoFormateado; ?> MXN</p>
                </div>
                 <div>
                    <span class="text-sm font-semibold text-gray-500 uppercase">Pagado a (Proveedor)</span>
                    <p class="text-lg text-gray-900 border-b border-dashed border-gray-400 pb-1"><?php echo $proveedor; ?></p>
                </div>
                 <div>
                    <span class="text-sm font-semibold text-gray-500 uppercase">Método de Pago</span>
                    <p class="text-lg text-gray-900 border-b border-dashed border-gray-400 pb-1"><?php echo $metodo; ?></p>
                </div>
                <div class="md:col-span-2">
                    <span class="text-sm font-semibold text-gray-500 uppercase">Cantidad con Letra</span>
                    <p class="text-lg text-gray-900 border-b border-dashed border-gray-400 pb-1 italic"><?php echo $cantidadConLetra; ?></p>
                </div>
            </section>

            <!-- === Descripción === -->
            <section class="mt-6">
                <div>
                    <span class="text-sm font-semibold text-gray-500 uppercase">Descripción</span>
                    <div class="mt-1 p-3 bg-gray-50 rounded-md border border-gray-200 min-h-[80px]">
                        <p class="text-gray-900">
                            <?php echo nl2br($descripcion); // nl2br para respetar saltos de línea ?>
                        </p>
                    </div>
                </div>
            </section>

            <!-- === Firmas === -->
            <footer class="mt-16 pt-8 flex flex-col md:flex-row justify-between items-end gap-8">
                 <div class="w-full md:w-1/2 text-left text-xs text-gray-500">
                    <p>Este documento es un comprobante interno de egreso del Instituto Universitario Morelia.</p>
                </div>
                <div class="w-full md:w-1/2 text-center">
                     <div class="w-24 h-8 ium-red flex items-center justify-center rounded mx-auto mb-2">
                        <span class="text-white font-bold text-xs">IUM</span>
                    </div>
                    <div class="h-12"></div> <!-- Espacio para firma -->
                    <div class="border-t border-gray-400 pt-2 mt-12">
                        <span class="text-sm font-semibold text-gray-800 uppercase">Firma de quien recibió</span>
                        <p class="text-lg text-gray-900 font-semibold mt-2">
                            <?php echo $destinatario; ?>
                        </p>
                    </div>
                </div>
            </footer>

        </div>
    </div>
</body>
</html>