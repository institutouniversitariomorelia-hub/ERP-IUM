<?php
// cli_get_all_presupuestos.php - devuelve JSON de presupuestos usando el modelo
if (session_status() === PHP_SESSION_NONE) session_start();
// Usar un usuario existente
$_SESSION['user_id'] = 1;
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../models/PresupuestoModel.php';

$model = new PresupuestoModel($conn);
$pres = $model->getAllPresupuestos();
// imprimir JSON
header('Content-Type: application/json');
echo json_encode($pres, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
