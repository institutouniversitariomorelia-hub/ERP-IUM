<?php
$password_plana = 'admin123'; // La contraseña que quieres verificar
$hash = password_hash($password_plana, PASSWORD_DEFAULT);
echo "<h1>Hash para 'admin123'</h1>";
echo "<p>Verifica que este hash sea EXACTAMENTE igual al guardado en la BD para 'su_admin':</p>";
echo "<hr>";
echo "<pre style='font-size: 1.2em; background-color: #eee; padding: 10px; border: 1px solid #ccc; word-wrap: break-word;'>" . htmlspecialchars($hash) . "</pre>";
?>