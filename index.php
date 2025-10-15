<?php
// ===============================================
// Archivo: index.php (El Router Principal)
// FIX: La lógica de $vista_a_cargar debe ser estable.
// ===============================================

ini_set('display_errors', 1);
error_reporting(E_ALL);

// 1. Incluir el controlador
require_once __DIR__ . "/controladores/vistasControlador.php";

// 2. Crear instancia del controlador
$controlador = new vistasControlador();

// 3. Obtener la RUTA del archivo a cargar (ejemplo: "LOGIN/login.html" o "vistas/plantilla.php")
$ruta_a_cargar = $controlador->obtener_vistas_controlador(); // Esto devuelve la RUTA

// 4. Obtener el nombre del MÓDULO (necesario para plantilla.php)
// Si views está presente, usamos su valor. Si no existe (estamos en el login), usamos 'mi_perfil' por defecto.
$vista_a_cargar = $_GET['views'] ?? 'mi_perfil'; 
// NOTA: Si vienes del login, $_GET['views'] SÍ existe y es 'mi_perfil', lo cual es correcto.

// 5. Decidir qué cargar:
if ($ruta_a_cargar == "404") {
    // Manejo de error 404
    echo "<!DOCTYPE html><html><head><title>404</title><style>body{font-family:sans-serif;text-align:center;padding-top:100px;}h1{color:#E70000;}</style></head><body><h1>404 | PÁGINA NO ENCONTRADA</h1><p>La vista solicitada no existe o no está autorizada.</p></body></html>";
} else {
    // Si la ruta es la plantilla, la incluimos.
    $ruta_final = __DIR__ . "/" . $ruta_a_cargar;
    
    // Incluir el archivo (login.html o plantilla.php)
    include $ruta_final; 
}