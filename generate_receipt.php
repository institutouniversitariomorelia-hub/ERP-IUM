<?php
/**
 * Enrutador de recibos
 * Redirige a la plantilla correcta según el tipo de ingreso o egreso
 */

require_once __DIR__ . '/config/database.php';

// Parámetros
$folio = isset($_GET['folio']) ? (int)$_GET['folio'] : 0;
$tipo = isset($_GET['tipo']) ? $_GET['tipo'] : ''; // 'ingreso' o 'egreso'
$reimpresion = isset($_GET['reimpresion']) ? (int)$_GET['reimpresion'] : 0;

if ($folio <= 0) {
    http_response_code(400);
    echo "Folio inválido.";
    exit;
}

// Determinar qué recibo generar
if ($tipo === 'egreso') {
    // Redirigir al recibo de egreso
    header("Location: src/Egresos/Receipts/egreso.php?folio=$folio" . ($reimpresion ? "&reimpresion=1" : ""));
    exit;
}

// Para ingresos, determinar el tipo según el concepto de la categoría
$sql = "SELECT i.*, c.concepto 
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
$conn->close();

if (!$ingreso) {
    echo "Recibo no encontrado para folio: " . htmlspecialchars($folio);
    exit;
}

// Redirigir según el concepto de la categoría
$concepto = $ingreso['concepto'] ?? '';
$reimpresionParam = $reimpresion ? "&reimpresion=1" : "";

switch ($concepto) {
    case 'Registro Diario':
        header("Location: src/Ingresos/Receipts/ingreso_diario.php?folio=$folio$reimpresionParam");
        break;
    
    case 'Titulaciones':
        header("Location: src/Ingresos/Receipts/ingreso_titulacion.php?folio=$folio$reimpresionParam");
        break;
    
    case 'Inscripciones y Reinscripciones':
        header("Location: src/Ingresos/Receipts/ingreso_inscripcion.php?folio=$folio$reimpresionParam");
        break;
    
    default:
        // Fallback al recibo genérico (registro diario)
        header("Location: src/Ingresos/Receipts/ingreso_diario.php?folio=$folio$reimpresionParam");
        break;
}
exit;
