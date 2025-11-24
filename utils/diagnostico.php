<?php
// Script de diagnóstico rápido
require_once __DIR__ . '/../config/database.php';

echo "<h2>Diagnóstico del Sistema</h2>";

// 1. Verificar categorías
$result = $conn->query("SELECT COUNT(*) as total FROM categorias");
$row = $result->fetch_assoc();
echo "<h3>Categorías: " . $row['total'] . "</h3>";

$result = $conn->query("SELECT id_categoria, nombre, tipo, concepto, no_borrable FROM categorias ORDER BY tipo, nombre LIMIT 20");
echo "<table border='1'><tr><th>ID</th><th>Nombre</th><th>Tipo</th><th>Concepto</th><th>Protegida</th></tr>";
while ($cat = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $cat['id_categoria'] . "</td>";
    echo "<td>" . $cat['nombre'] . "</td>";
    echo "<td>" . $cat['tipo'] . "</td>";
    echo "<td>" . ($cat['concepto'] ?? '-') . "</td>";
    echo "<td>" . ($cat['no_borrable'] ? 'SÍ' : 'NO') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 2. Verificar presupuestos
$result = $conn->query("SELECT COUNT(*) as total FROM presupuestos");
$row = $result->fetch_assoc();
echo "<h3>Presupuestos: " . $row['total'] . "</h3>";

$result = $conn->query("SELECT id_presupuesto, nombre, monto_limite, id_categoria FROM presupuestos LIMIT 10");
echo "<table border='1'><tr><th>ID</th><th>Nombre</th><th>Monto</th><th>ID Categoría</th></tr>";
while ($pres = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $pres['id_presupuesto'] . "</td>";
    echo "<td>" . ($pres['nombre'] ?? 'N/A') . "</td>";
    echo "<td>" . $pres['monto_limite'] . "</td>";
    echo "<td>" . ($pres['id_categoria'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 3. Verificar ingresos
$result = $conn->query("SELECT COUNT(*) as total FROM ingresos");
$row = $result->fetch_assoc();
echo "<h3>Ingresos: " . $row['total'] . "</h3>";

// 4. Verificar egresos
$result = $conn->query("SELECT COUNT(*) as total FROM egresos");
$row = $result->fetch_assoc();
echo "<h3>Egresos: " . $row['total'] . "</h3>";

// 5. Verificar estructura de tabla ingresos
echo "<h3>Estructura tabla ingresos:</h3>";
$result = $conn->query("DESCRIBE ingresos");
echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($field = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $field['Field'] . "</td>";
    echo "<td>" . $field['Type'] . "</td>";
    echo "<td>" . $field['Null'] . "</td>";
    echo "<td>" . $field['Key'] . "</td>";
    echo "<td>" . ($field['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// 6. Verificar estructura de tabla egresos
echo "<h3>Estructura tabla egresos:</h3>";
$result = $conn->query("DESCRIBE egresos");
echo "<table border='1'><tr><th>Campo</th><th>Tipo</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($field = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $field['Field'] . "</td>";
    echo "<td>" . $field['Type'] . "</td>";
    echo "<td>" . $field['Null'] . "</td>";
    echo "<td>" . $field['Key'] . "</td>";
    echo "<td>" . ($field['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>
