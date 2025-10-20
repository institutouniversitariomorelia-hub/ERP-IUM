<?php
// ===============================================
// Archivo: vistas/plantilla.php (Layout Master)
// Mantiene las rutas absolutas para los recursos.
// ===============================================

// La variable $vista_a_cargar (ej: "mi_perfil") debe estar definida por index.php
if (!isset($vista_a_cargar) || $vista_a_cargar === "404") {
    $vista_a_cargar = "mi_perfil"; // Fallback por defecto
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ERP IUM - Sistema de Control</title>

    <link rel="icon" href="/Curso/RECURSOS/favicon1.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    
    <style>
        :root { --bs-danger: #E70000; }
        body { background-color: #37ca3cff; }
        #sidebar {
            width: 250px; height: 100vh; position: fixed;
            background-color: var(--bs-danger);
            padding: 0; z-index: 1000;
            box-shadow: 2px 0 5px rgba(0,0,0,.3);
            overflow-y: auto;
        }
        #content { margin-left: 250px; min-height: 100vh; }
        #sidebar a {
            padding: 12px 20px; text-decoration: none;
            display: block; color: rgba(255,255,255,0.85);
            border-left: 3px solid transparent;
            transition: all 0.2s;
        }
        #sidebar a:hover, #sidebar a.sidebar-active {
            color: #fff; background: rgba(0,0,0,0.1);
            border-left: 3px solid white;
        }
        .sidebar-header { padding: 20px 15px; text-align: center; }
        .sidebar-header img { max-width: 100%; height: auto; }

        #header-nav {
            background-color: #fff; border-bottom: 1px solid #ddd;
            box-shadow: 0 2px 4px rgba(0,0,0,.1); padding: 0 15px;
            position: sticky; top: 0; z-index: 999;
        }
        .header-logo { height: 40px; margin-right: 10px; }
        .btn-logout { background-color: var(--bs-danger); color: white; border: none; }
        .btn-logout:hover { background-color: #b80000; }

        .main-content { padding: 20px; }
    </style>
</head>
<body>

    <div id="sidebar">
        <div class="sidebar-header">
            <img src="/Curso/RECURSOS/LOGO ium blanco (1).png" alt="IUM Logo Blanco">
        </div>

        <nav class="nav flex-column">
            <h6 class="text-white text-uppercase px-3 mt-4 mb-1">Gestión</h6>

            <a href="index.php?views=mi_perfil" data-module="mi_perfil"><ion-icon name="person-circle-outline"></ion-icon> Mi Perfil</a>
            <a href="index.php?views=egresos" data-module="egresos"><ion-icon name="wallet-outline"></ion-icon> Egresos</a>
            <a href="index.php?views=ingresos" data-module="ingresos"><ion-icon name="trending-up-outline"></ion-icon> Ingresos</a>
            <a href="index.php?views=presupuestos" data-module="presupuestos"><ion-icon name="analytics-outline"></ion-icon> Presupuestos</a>
            <a href="index.php?views=categorias" data-module="categorias"><ion-icon name="pricetags-outline"></ion-icon> Categorías</a>

            <h6 class="text-white text-uppercase px-3 mt-4 mb-1">Administración</h6>
            <a href="index.php?views=auditoria" data-module="auditoria"><ion-icon name="shield-checkmark-outline"></ion-icon> Auditoría</a>
        </nav>
    </div>

    <div id="content">
        <nav id="header-nav" class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <img src="/Curso/RECURSOS/logo ium rojo (3).png" alt="Logo IUM Rojo" class="header-logo">
                <span class="navbar-brand text-danger fw-bold">ERP IUM</span>

                <div class="collapse navbar-collapse justify-content-end">
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-dark" href="#" data-bs-toggle="dropdown">
                                Super Administrador
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="index.php?views=mi_perfil">Mi Perfil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="index.php">
                                        <ion-icon name="log-out-outline"></ion-icon> Cerrar Sesión
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <main class="main-content">
        <?php
            // La variable $vista_a_cargar (ej: "mi_perfil") llega desde index.php
            
            // 1. Validar que la vista solicitada no sea "login"
            if ($vista_a_cargar === "login") {
                // Si la plantilla se carga accidentalmente con views=login, forzamos mi_perfil
                $archivo_modulo = "mi_perfil.php"; 
            } else {
                // Si es cualquier otra vista válida (mi_perfil, egresos, etc.)
                $archivo_modulo = $vista_a_cargar . ".php"; 
            }
            
            // Construimos la ruta absoluta al módulo dentro de la carpeta 'vistas/'
            // Usamos __DIR__ para plantillas, ya que es el directorio de vistas/
            $ruta_final = __DIR__ . "/" . $archivo_modulo;

            if (file_exists($ruta_final)) {
                // Esto incluye el contenido del módulo (ej: mi_perfil.php)
                include $ruta_final; 
            } else {
                echo "<h3 class='text-danger text-center mt-5'>⚠️ Error Fatal: El archivo de módulo ($archivo_modulo) no se encontró físicamente en la carpeta 'vistas/'.</h3>";
                echo "<p class='text-center'>Ruta de búsqueda: " . htmlspecialchars($ruta_final) . "</p>";
            }
        ?>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Resalta el módulo activo
        $(document).ready(function(){
            const params = new URLSearchParams(window.location.search);
            // Si no hay 'views', el activo es mi_perfil
            const activeView = params.get('views') || 'mi_perfil'; 
            
            // Itera sobre todos los enlaces del sidebar
            $('#sidebar a').each(function(){
                if ($(this).data('module') === activeView) {
                    $(this).addClass('sidebar-active');
                }
            });
        });
    </script>
</body>
</html>