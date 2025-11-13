<?php
// test_presupuesto_save.php - prueba rápida para savePresupuesto
// for CLI ensure session user (set BEFORE db.php so the MySQL session variable @auditoria_user_id is set)
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user_id'] = 1;

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/PresupuestoModel.php';

$model = new PresupuestoModel($conn);

$data = [
    'monto_limite' => '123456.00',
    'fecha' => date('Y-m-d'),
    'id_user' => 1,
    // 'id_categoria' => null, // general
    // 'parent_presupuesto' => null
];

try {
    $ok = $model->savePresupuesto($data, null);
    if ($ok) {
        echo "SUCCESS: Presupuesto guardado correctamente\n";
    } else {
        echo "FAIL: savePresupuesto devolvió false\n";
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage() . "\n";
}

// Mostrar último error de conexión / consulta
if (isset($conn) && $conn instanceof mysqli) {
    if ($conn->error) echo "DB ERROR (conn): " . $conn->error . "\n";
}
?>