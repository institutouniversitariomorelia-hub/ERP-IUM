<?php
// Test de paths después de la reorganización

echo "<h2>Test de Rutas</h2>";

// Test 1: Config
$configPath = __DIR__ . '/config/database.php';
echo "1. Config: " . (file_exists($configPath) ? "✓" : "✗") . " - $configPath<br>";

// Test 2: UserController
$userControllerPath = __DIR__ . '/src/Auth/Controllers/UserController.php';
echo "2. UserController: " . (file_exists($userControllerPath) ? "✓" : "✗") . " - $userControllerPath<br>";

// Test 3: UserModel
$userModelPath = __DIR__ . '/src/Auth/Models/UserModel.php';
echo "3. UserModel: " . (file_exists($userModelPath) ? "✓" : "✗") . " - $userModelPath<br>";

// Test 4: Helpers
$helpersPath = __DIR__ . '/shared/Helpers/helpers.php';
echo "4. Helpers: " . (file_exists($helpersPath) ? "✓" : "✗") . " - $helpersPath<br>";

// Test 5: Layout
$layoutPath = __DIR__ . '/shared/Views/layout.php';
echo "5. Layout: " . (file_exists($layoutPath) ? "✓" : "✗") . " - $layoutPath<br>";

// Test 6: Intentar cargar config
echo "<br><h3>Intentando cargar config/database.php...</h3>";
try {
    require_once __DIR__ . '/config/database.php';
    echo "✓ Config cargado exitosamente<br>";
    echo "✓ Conexión BD: " . (isset($conn) ? "Conectado" : "No conectado") . "<br>";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "<br>";
}

// Test 7: __DIR__ desde UserController
echo "<br><h3>Simulando path desde UserController...</h3>";
$simulatedDir = 'C:\xampp\htdocs\ERP-IUM\src\Auth\Controllers';
$relativePath = $simulatedDir . '/../Models/UserModel.php';
$normalizedPath = realpath($relativePath);
echo "Path relativo: $relativePath<br>";
echo "Path normalizado: $normalizedPath<br>";
echo "Existe: " . (file_exists($normalizedPath) ? "✓ SI" : "✗ NO") . "<br>";
?>
