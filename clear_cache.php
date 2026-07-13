<?php
// Script para limpiar caché de PHP OPCache y archivo
// Forzar que PHP recompile el código

// 1. Limpiar OPCache si está disponible
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OPcache limpiado<br>";
}

// 2. Limpiar opcache por archivo específico
$filesToClear = [
    __DIR__ . '/src/Presupuestos/Controllers/PresupuestoController.php',
    __DIR__ . '/src/Presupuestos/Models/PresupuestoModel.php',
    __DIR__ . '/src/Egresos/Controllers/EgresoController.php',
    __DIR__ . '/src/Categorias/Models/CategoriaModel.php',
];

if (function_exists('opcache_invalidate')) {
    foreach ($filesToClear as $file) {
        if (file_exists($file)) {
            opcache_invalidate($file, true);
            echo "✓ Invalidado: " . basename($file) . "<br>";
        }
    }
}

// 3. Estadísticas
if (function_exists('opcache_get_status')) {
    $status = opcache_get_status();
    if ($status) {
        echo "<hr>";
        echo "OPCache activo: " . ($status['opcache_enabled'] ? 'Sí' : 'No') . "<br>";
        echo "Memoria usada: " . round($status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB<br>";
    }
}

echo "<hr>";
echo "<a href='index.php?controller=egreso&action=index'>Volver a Egresos</a>";
?>
