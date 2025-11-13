<?php
// views/layout.php (CORREGIDO 'anio' EN MODAL INGRESO)

// 1. VERIFICAR SESIÓN Y DEFINIR $currentUser DE FORMA SEGURA AL INICIO
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'index.php?controller=auth&action=login');
    exit;
}
$currentUser = [
    'id' => $_SESSION['user_id'],
    'nombre' => $_SESSION['user_nombre'],
    'username' => $_SESSION['user_username'],
    'rol' => $_SESSION['user_rol']
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'ERP IUM'; ?></title>
    <link rel="icon" href="<?php echo BASE_URL; ?>public/logo ium blanco.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>

    <style>
         :root { --c-primario: #B80000; --c-primario-hover: #9A0000; --c-fondo: #f4f6f9; --c-blanco: #ffffff; --c-texto-principal: #343a40; --c-texto-secundario: #6c757d; --sombra-suave: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1); }
        body { background-color: var(--c-fondo); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
    #sidebar { width: 280px; height: 100vh; position: fixed; background: linear-gradient(180deg, #B80000 0%, #950000 50%, #800000 100%); padding: 0; z-index: 1000; box-shadow: 2px 0 15px rgba(0,0,0,0.15); display: flex; flex-direction: column; transition: transform 0.25s ease-in-out, left 0.25s ease-in-out; overflow-y: auto; }
        
        /* Logo Container */
        #sidebar .logo-container { padding: 1.5rem 1rem; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.15); background: rgba(255, 255, 255, 0.05); }
        #sidebar .sidebar-logo { max-height: 50px; width: auto; margin-bottom: 8px; filter: brightness(1.1); }
        #sidebar .sidebar-title { color: white; font-size: 1.1rem; font-weight: 600; margin: 0; margin-bottom: 2px; }
        #sidebar .sidebar-subtitle { color: rgba(255, 255, 255, 0.7); font-size: 0.75rem; margin: 0; line-height: 1.2; }
        
        /* Sidebar Sections */
        #sidebar .sidebar-section { margin-bottom: 1.5rem; }
        #sidebar .sidebar-section-title { padding: 0.5rem 1rem; color: rgba(255, 255, 255, 0.6); font-size: 0.7rem; font-weight: 600; letter-spacing: 1px; text-transform: uppercase; border-bottom: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 0.5rem; }
        
        /* Navigation Links */
        #sidebar .sidebar-nav { padding: 0; }
        #sidebar .nav-link { color: rgba(255, 255, 255, 0.85); padding: 0.75rem 1rem; margin: 0 0.5rem; border-radius: 8px; transition: all 0.3s ease; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; text-decoration: none; position: relative; }
        #sidebar .nav-link ion-icon { font-size: 1.1rem; margin-right: 12px; min-width: 20px; }
        #sidebar .nav-link span { flex: 1; }
        #sidebar .nav-link:hover { background: rgba(255, 255, 255, 0.1); color: white; transform: translateX(2px); }
        #sidebar .nav-link.active { background: rgba(255, 255, 255, 0.15); color: white; font-weight: 500; }
        #sidebar .nav-link.active::before { content: ''; position: absolute; left: 0; top: 50%; transform: translateY(-50%); width: 3px; height: 20px; background: white; border-radius: 0 3px 3px 0; }
        
        /* User Profile Link - Special styling */
        #sidebar .nav-link .user-avatar { color: rgba(255, 255, 255, 0.9); }
        
    #main-content { margin-left: 280px; width: calc(100% - 280px); transition: margin-left 0.25s ease-in-out; }
    /* Sidebar collapsed / mobile behaviour */
    #sidebar.open { transform: translateX(0); left: 0; }
    #sidebar.closed { transform: translateX(-100%); left: -100%; }
    .sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:900; }
        .top-header { background-color: var(--c-primario); color: white; padding: 10px 30px; font-size: 1.1rem; border-bottom: 1px solid #dee2e6; box-shadow: var(--sombra-suave); position: sticky; top: 0; z-index: 999; display: flex; justify-content: space-between; align-items: center; gap: 1rem; }
        .user-info-header { flex-shrink: 0; }
        #view-container { padding: 30px; }
        .card { border: none; border-radius: 8px; box-shadow: var(--sombra-suave); margin-bottom: 1.5rem; }
        .card-header { background-color: var(--c-blanco); font-weight: bold; border-bottom: 1px solid #f0f0f0; padding: 1rem 1.25rem; }
        .table { border-collapse: separate; border-spacing: 0; }
        .table thead th { background-color: #f8f9fa; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; color: var(--c-texto-secundario); border-bottom-width: 1px; padding: 0.75rem; vertical-align: bottom;}
        .table tbody tr:hover { background-color: #f1f1f1; }
    .clickable-row { cursor: pointer; }
        .table td, .table th { padding: 0.75rem; vertical-align: middle; }
    /* Improve wrap behavior on small screens */
    .table td, .table th { word-break: break-word; white-space: normal; }
        .btn { border-radius: 6px; transition: all 0.2s ease-in-out; padding: 0.375rem 0.75rem; font-size: 0.9rem;}
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.8rem; }
        .btn-danger { background-color: var(--c-primario); border-color: var(--c-primario); }
        .btn-danger:hover { background-color: var(--c-primario-hover); border-color: var(--c-primario-hover); transform: translateY(-1px); box-shadow: var(--sombra-suave);}
        .modal-content { border: none; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .modal-header-danger { background-color: var(--c-primario); color: white; border-top-left-radius: 7px; border-top-right-radius: 7px;}
        .modal-title { font-weight: 500; }
        .badge { padding: 0.4em 0.6em; }
        /* Small screen adjustments */
        @media (max-width: 991px) {
            .top-header { padding: 8px 12px; font-size: 0.95rem; gap: 0.5rem; }
            .top-header .me-auto { font-size: 0.9rem; }
            #view-container { padding: 12px; }
            .card { margin-bottom: 1rem; width: 100%; }
            .table thead th { font-size: 0.72rem; }
            .table td, .table th { padding: 0.5rem; }
            .btn { padding: 0.35rem 0.6rem; font-size: 0.85rem; }
            /* Sidebar behaviour on small screens: act as overlay panel without pushing content */
            #sidebar { width: 85%; max-width: 320px; left: 0; transform: translateX(-110%); position: fixed; top: 0; height: 100vh; z-index: 1100; border-right: 1px solid rgba(0,0,0,0.06); border-top-right-radius: 8px; background-color: var(--c-primario) !important; color: #fff !important; }
            #sidebar.open { transform: translateX(0); box-shadow: 0 10px 30px rgba(0,0,0,0.35); }
            /* Make sure the sidebar keeps the brand color and no white corners on phones */
            #sidebar, #sidebar .logo-container, #sidebar .nav { background-color: var(--c-primario) !important; color: #fff !important; border-radius: 0 !important; }
            #sidebar.closed { transform: translateX(-110%); }
            /* Keep the main content full width when sidebar is hidden */
            #main-content { margin-left: 0 !important; width: 100% !important; }
            /* Make nav links wrap instead of overflowing */
            #sidebar .nav-link { white-space: normal; padding: 10px 16px; }
            .logo-container img { max-height: 44px; }
            .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1050; }
            /* when overlay is active, JS sets overlay.style.display = 'block' */
        }

        /* Extra small screens: tweak spacing a bit more */
        @media (max-width: 575px) {
            .top-header { padding: 6px 8px; font-size: 0.85rem; }
            .top-header .me-auto { font-size: 0.85rem; }
            .top-header #btnToggleSidebar { padding: 0.2rem 0.4rem; margin-right: 0.25rem !important; }
            .table thead th { font-size: 0.68rem; }
            .table td, .table th { padding: 0.4rem; }
            .logo-container img { max-height: 40px; }
        }

        /* Column helpers */
        .col-id { width: 5%; }
        @media (max-width: 767px) { .col-id { width: auto; } }

        /* Make action buttons full width on small screens and auto on md+ */
        .btn-responsive-sm { display: flex; flex-direction: column; gap: .5rem; }
        .btn-responsive-sm .btn { width: 100%; }
        @media (min-width: 768px) {
            .btn-responsive-sm { flex-direction: row; gap: .5rem; }
            .btn-responsive-sm .btn { width: auto; }
        }

        /* Ensure card headers are full width and not floated on small screens */
        @media (max-width: 991px) {
            .card .card-header { display: block; width: 100%; box-sizing: border-box; }
        }

        /* Auditoría helpers */
        #aud_raw_consulta { display:none; white-space: pre-wrap; background:#f8f9fa; padding:10px; border-radius:4px; font-family: monospace; font-size: 0.9em; }
        .aud-action-badge { font-weight:600; font-size:0.85em; }
        .aud-row-insert { background: rgba(198, 239, 206, 0.25); }
        .aud-row-update { background: rgba(255, 243, 205, 0.25); }
        .aud-row-delete { background: rgba(248, 215, 218, 0.25); }
    </style>
</head>
<body>
    <div id="sidebar">
        <!-- Header del Sidebar -->
        <div class="logo-container" id="logoContainer" style="cursor: pointer;">
            <img src="<?php echo BASE_URL; ?>public/logo ium blanco.png" alt="Logo IUM" class="sidebar-logo">
            <h6 class="sidebar-title">Sistema ERP</h6>
            <p class="sidebar-subtitle">Instituto Universitario Morelia</p>
        </div>
        
        <!-- User Info Section (Moved from Footer) -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">
                <span>MENÚ PRINCIPAL</span>
            </div>
            <ul class="nav flex-column sidebar-nav">
                <?php $activeModule = $activeModule ?? ''; ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $activeModule === 'profile' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php?controller=user&action=profile">
                        <div class="user-avatar" style="display: inline-block; margin-right: 12px;">
                            <ion-icon name="person-circle" style="font-size: 2rem; vertical-align: middle;"></ion-icon>
                        </div>
                        <div style="display: inline-block; vertical-align: middle;">
                            <div style="font-weight: 500; font-size: 0.9rem;"><?php echo htmlspecialchars($currentUser['nombre']); ?></div>
                            <div style="font-size: 0.75rem; opacity: 0.8;"><?php echo htmlspecialchars($currentUser['rol']); ?></div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Sección Financiera -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">
                <span>GESTIÓN FINANCIERA</span>
            </div>
            <ul class="nav flex-column sidebar-nav">
                <?php if (roleCanViewModule('dashboard')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $activeModule === 'dashboard' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php?controller=dashboard&action=index">
                            <ion-icon name="stats-chart-outline"></ion-icon>
                            <span>Dashboard</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (roleCanViewModule('ingresos')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $activeModule === 'ingresos' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php?controller=ingreso&action=index">
                            <ion-icon name="trending-up-outline"></ion-icon>
                            <span>Ingresos</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (roleCanViewModule('egresos')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $activeModule === 'egresos' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php?controller=egreso&action=index">
                            <ion-icon name="trending-down-outline"></ion-icon>
                            <span>Egresos</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (roleCanViewModule('presupuestos')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $activeModule === 'presupuestos' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php?controller=presupuesto&action=index">
                            <ion-icon name="wallet-outline"></ion-icon>
                            <span>Presupuestos</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (roleCanViewModule('ingresos') || roleCanViewModule('egresos')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $activeModule === 'reportes' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php?controller=reporte&action=index">
                            <ion-icon name="document-text-outline"></ion-icon>
                            <span>Reportes</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Sección Configuración -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">
                <span>CONFIGURACIÓN</span>
            </div>
            <ul class="nav flex-column sidebar-nav">
                <?php if (roleCanViewModule('categorias')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $activeModule === 'categorias' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php?controller=categoria&action=index">
                            <ion-icon name="pricetags-outline"></ion-icon>
                            <span>Categorías</span>
                        </a>
                    </li>
                <?php endif; ?>
                <?php if (roleCanViewModule('auditoria')): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $activeModule === 'auditoria' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php?controller=auditoria&action=index">
                            <ion-icon name="layers-outline"></ion-icon>
                            <span>Historial Auditoría</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>

    </div>

    <div id="main-content">
        <header class="top-header d-flex align-items-center">
            <button id="btnToggleSidebar" class="btn btn-sm btn-outline-light d-lg-none me-2" type="button" aria-label="Abrir menú">
                <ion-icon name="menu-outline"></ion-icon>
            </button>
            <span class="me-auto fw-bold">
                <span class="d-none d-md-inline">SISTEMA DE CONTROL DE INGRESOS Y EGRESOS IUM</span>
                <span class="d-md-none">Sistema ERP</span>
            </span>
            <div class="user-info-header">
                <div class="d-none d-md-block">
                    <span class="fs-6 fw-normal">Usuario: <strong><?php echo htmlspecialchars($currentUser['nombre']); ?></strong></span>
                </div>
                <div class="d-md-none text-end">
                    <div class="fw-bold" style="font-size: 0.85rem; line-height: 1.2;">
                        <?php echo htmlspecialchars($currentUser['username']); ?>
                    </div>
                    <div style="font-size: 0.7rem; opacity: 0.9; line-height: 1.2;">
                        <?php 
                        $roles = ['SU' => 'Super Admin', 'ADM' => 'Admin', 'COB' => 'Cobranzas', 'REC' => 'Rectoría'];
                        echo $roles[$currentUser['rol']] ?? $currentUser['rol']; 
                        ?>
                    </div>
                </div>
                    <!-- Modal: Mostrar respuesta cruda (para debugging AJAX) -->
                    <div class="modal fade" id="modalRawResponse" tabindex="-1" aria-labelledby="modalRawResponseLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header modal-header-danger">
                                    <h5 class="modal-title" id="modalRawResponseLabel">Respuesta cruda del servidor</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="small text-muted">Se muestran las respuestas más recientes de los endpoints relacionados al modal de Presupuesto por Categoría.</p>
                                    <h6>getAllCategorias</h6>
                                    <pre id="raw_response_cats" style="white-space: pre-wrap; background:#f8f9fa; padding:12px; border-radius:6px; max-height:300px; overflow:auto;"></pre>
                                    <h6 class="mt-3">getAllPresupuestos</h6>
                                    <pre id="raw_response_pres" style="white-space: pre-wrap; background:#f8f9fa; padding:12px; border-radius:6px; max-height:300px; overflow:auto;"></pre>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </header>
        <main id="view-container" class="p-4">
            <?php
            if (isset($content)) { echo $content; } 
            else { echo '<div class="alert alert-danger">Error: Contenido de la vista no encontrado.</div>'; }
            ?>
        </main>
    </div>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Modal: Editar Mi Perfil -->
    <div class="modal fade" id="modalEditarMiPerfil" tabindex="-1" aria-labelledby="modalEditarMiPerfilLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">
                <form id="formEditarMiPerfil">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title" id="modalEditarMiPerfilTitle">Editar Mi Perfil</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="perfil_id" name="id">
                        <div class="mb-3">
                            <label for="perfil_nombre" class="form-label">Nombre Completo</label>
                            <input id="perfil_nombre" name="nombre" type="text" class="form-control" required>
                        </div>
                        <?php if ($currentUser['rol'] === 'SU'): ?>
                            <!-- Solo el Super Usuario puede cambiar username y rol -->
                            <div class="mb-3">
                                <label for="perfil_username" class="form-label">Usuario (username)</label>
                                <input id="perfil_username" name="username" type="text" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="perfil_rol" class="form-label">Rol Asignado</label>
                                <select id="perfil_rol" name="rol" class="form-select" required>
                                    <option value="ADM">Administración (ADM)</option>
                                    <option value="COB">Cobranzas (COB)</option>
                                    <option value="REC">Rectoría (REC)</option>
                                    <option value="SU">SUPER USUARIO (SU)</option>
                                </select>
                            </div>
                        <?php else: ?>
                            <!-- Otros roles: campos de solo lectura -->
                            <div class="mb-3">
                                <label for="perfil_username_readonly" class="form-label">Usuario (username)</label>
                                <input id="perfil_username_readonly" type="text" class="form-control" readonly disabled>
                                <input type="hidden" id="perfil_username" name="username">
                            </div>
                            <div class="mb-3">
                                <label for="perfil_rol_readonly" class="form-label">Rol Asignado</label>
                                <input id="perfil_rol_readonly" type="text" class="form-control" readonly disabled>
                                <input type="hidden" id="perfil_rol" name="rol">
                            </div>
                        <?php endif; ?>
                        <hr>
                        <div class="d-grid">
                            <button type="button" class="btn btn-outline-secondary" id="btnAbrirCambiarPassword">
                                <ion-icon name="key-outline" style="vertical-align: middle;"></ion-icon> Cambiar Contraseña
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Registrar Nuevo Usuario -->
    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">
                <form id="formUsuario">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title" id="modalUsuarioTitle">Registrar Nuevo Usuario</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="usuario_id" name="id">
                        <div class="mb-3">
                            <label for="usuario_nombre" class="form-label">Nombre Completo</label>
                            <input id="usuario_nombre" name="nombre" type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="usuario_username" class="form-label">Usuario (username)</label>
                            <input id="usuario_username" name="username" type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="usuario_password" class="form-label">Contraseña</label>
                            <input id="usuario_password" name="password" type="password" class="form-control" required>
                            <div class="form-text">Obligatorio para usuarios nuevos.</div>
                        </div>
                        <div class="mb-3">
                            <label for="usuario_rol" class="form-label">Asignar Rol</label>
                            <select id="usuario_rol" name="rol" class="form-select" required>
                                <option value="ADM">Administración (ADM)</option>
                                <option value="COB">Cobranzas (COB)</option>
                                <option value="REC">Rectoría (REC)</option>
                                <option value="SU">SUPER USUARIO (SU)</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Crear Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Cambiar Contraseña (Nueva Versión) -->
    <div class="modal fade" id="modalCambiarPasswordNuevo" tabindex="-1" aria-labelledby="modalCambiarPasswordNuevoLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">
                <form id="formCambiarPasswordNuevo">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title" id="modalCambiarPasswordNuevoLabel">Cambiar Contraseña</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="changepass_username" name="username">
                        <div class="mb-3">
                            <label for="password_actual" class="form-label">Contraseña Actual</label>
                            <div class="input-group">
                                <input id="password_actual" name="password_actual" type="password" class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordActual">
                                    <ion-icon name="eye-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password_nueva" class="form-label">Contraseña Nueva</label>
                            <div class="input-group">
                                <input id="password_nueva" name="password_nueva" type="password" class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordNueva">
                                    <ion-icon name="eye-outline"></ion-icon>
                                </button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirmar" class="form-label">Confirmar Contraseña Nueva</label>
                            <div class="input-group">
                                <input id="password_confirmar" name="password_confirmar" type="password" class="form-control" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirmar">
                                    <ion-icon name="eye-outline"></ion-icon>
                                </button>
                            </div>
                            <div class="form-text" id="passwordMatchMessage"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger" id="btnGuardarPasswordNueva">Cambiar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Cambiar Contraseña (Versión Antigua para Admin) -->
    <div class="modal fade" id="modalCambiarPassword" tabindex="-1" aria-labelledby="modalCambiarPasswordLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">
                <form id="formCambiarPassword">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title" id="modalCambiarPasswordLabel">Cambiar Contraseña</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="change_pass_username" name="username">
                        <div class="mb-3">
                            <label class="form-label">Nueva Contraseña para <strong id="username_display"></strong></label>
                            <input id="change_pass_password" name="password" type="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Guardar Contraseña</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEgreso" tabindex="-1" aria-labelledby="modalEgresoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-fullscreen-sm-down modal-dialog-scrollable">
            <div class="modal-content">
                            <form id="formEgreso">
                                <div class="modal-header modal-header-danger">
                                    <h5 class="modal-title" id="modalEgresoTitle">Registrar/Editar Egreso</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                                    <input type="hidden" id="egreso_id" name="id">
                                    <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($currentUser['id']); ?>">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label for="eg_fecha" class="form-label">Fecha <span class="text-danger">*</span></label>
                                            <input id="eg_fecha" name="fecha" type="date" class="form-control form-control-sm" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="eg_monto" class="form-label">Monto <span class="text-danger">*</span></label>
                                            <input id="eg_monto" name="monto" type="number" step="0.01" class="form-control form-control-sm" min="0.01" placeholder="Ej: 700.00" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="eg_id_categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                                            <select id="eg_id_categoria" name="id_categoria" class="form-select form-select-sm" required></select>
                                        </div>

                                        <div class="col-md-4">
                                            <label for="eg_id_presupuesto" class="form-label">Presupuesto <span class="text-danger">*</span></label>
                                            <select id="eg_id_presupuesto" name="id_presupuesto" class="form-select form-select-sm" required>
                                                <option value="">Cargando presupuestos...</option>
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <!-- spacer to keep layout -->
                                        </div>

                                        <div class="col-md-6">
                                            <label for="eg_proveedor" class="form-label">Proveedor</label>
                                            <input id="eg_proveedor" name="proveedor" type="text" class="form-control form-control-sm" placeholder="Nombre del proveedor">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="eg_destinatario" class="form-label">Destinatario <span class="text-danger">*</span></label>
                                            <input id="eg_destinatario" name="destinatario" type="text" class="form-control form-control-sm" placeholder="Persona o entidad que recibe el pago" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="eg_forma_pago" class="form-label">Forma de Pago <span class="text-danger">*</span></label>
                                            <select id="eg_forma_pago" name="forma_pago" class="form-select form-select-sm" required>
                                                <option value="">Seleccione...</option>
                                                <option value="Efectivo">Efectivo</option>
                                                <option value="Transferencia">Transferencia</option>
                                                <option value="Cheque">Cheque</option>
                                                <option value="Tarjeta D.">Tarjeta D.</option>
                                                <option value="Tarjeta C.">Tarjeta C.</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="eg_documento_de_amparo" class="form-label">Documento de Amparo</label>
                                            <input id="eg_documento_de_amparo" name="documento_de_amparo" type="text" class="form-control form-control-sm" placeholder="Ej: Factura A-123, Recibo 456">
                                        </div>
                                        <div class="col-md-4">
                                            <label for="eg_activo_fijo" class="form-label">¿Activo Fijo?</label>
                                            <select id="eg_activo_fijo" name="activo_fijo" class="form-select form-select-sm">
                                                <option value="NO" selected>NO</option>
                                                <option value="SI">SI</option>
                                            </select>
                                        </div>
                                        <div class="col-12">
                                            <label for="eg_descripcion" class="form-label">Descripción</label>
                                            <textarea id="eg_descripcion" name="descripcion" class="form-control form-control-sm" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger btn-sm">Guardar Egreso</button>
                                </div>
                            </form>
                        </div>
        </div>
    </div>

    <div class="modal fade" id="modalIngreso" tabindex="-1" aria-labelledby="modalIngresoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-fullscreen-sm-down modal-dialog-scrollable">
            <div class="modal-content">
                <form id="formIngreso">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title" id="modalIngresoTitle">Registrar/Editar Ingreso</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <input type="hidden" id="ingreso_id" name="id">
                        <div class="row g-3">
                            <div class="col-md-4"><label for="in_fecha" class="form-label">Fecha Pago <span class="text-danger">*</span></label><input id="in_fecha" name="fecha" type="date" class="form-control form-control-sm" required></div>
                            <div class="col-md-4"><label for="in_monto" class="form-label">Monto <span class="text-danger">*</span></label><input id="in_monto" name="monto" type="number" step="0.01" class="form-control form-control-sm" min="0.01" placeholder="Ej: 5000.00" required></div>
                            <div class="col-md-4"><label for="in_metodo_de_pago" class="form-label">Método Pago <span class="text-danger">*</span></label><select id="in_metodo_de_pago" name="metodo_de_pago" class="form-select form-select-sm" required><option value="">Seleccione...</option><option value="Efectivo">Efectivo</option><option value="Transferencia">Transferencia</option><option value="Depósito">Depósito</option></select></div>
                            <div class="col-md-8"><label for="in_alumno" class="form-label">Alumno <span class="text-danger">*</span></label><input id="in_alumno" name="alumno" type="text" class="form-control form-control-sm" placeholder="Nombre completo del alumno" required></div>
                            <div class="col-md-4"><label for="in_matricula" class="form-label">Matrícula <span class="text-danger">*</span></label><input id="in_matricula" name="matricula" type="text" class="form-control form-control-sm" required></div>
                            <div class="col-md-4"><label for="in_nivel" class="form-label">Nivel <span class="text-danger">*</span></label><select id="in_nivel" name="nivel" class="form-select form-select-sm" required><option value="">Seleccione...</option><option value="Licenciatura">Licenciatura</option><option value="Maestría">Maestría</option><option value="Doctorado">Doctorado</option></select></div>
                            <div class="col-md-5"><label for="in_programa" class="form-label">Programa <span class="text-danger">*</span></label><input id="in_programa" name="programa" type="text" class="form-control form-control-sm" placeholder="Ej: Lic. en Derecho" required></div>
                            <div class="col-md-3"><label for="in_grado" class="form-label">Grado</label><input id="in_grado" name="grado" type="number" min="1" max="15" class="form-control form-control-sm" placeholder="Ej: 1, 5"></div>
                            <div class="col-md-4"><label for="in_modalidad" class="form-label">Modalidad</label><select id="in_modalidad" name="modalidad" class="form-select form-select-sm"><option value="">N/A</option><option value="Cuatrimestral">Cuatrimestral</option><option value="Semestral">Semestral</option></select></div>
                            <div class="col-md-4"><label for="in_grupo" class="form-label">Grupo</label><input id="in_grupo" name="grupo" type="text" class="form-control form-control-sm" placeholder="Ej: A, 101"></div>
                            <div class="col-md-4"><label for="in_id_categoria" class="form-label">Categoría <span class="text-danger">*</span></label><select id="in_id_categoria" name="id_categoria" class="form-select form-select-sm" required></select></div>
                            <div class="col-md-6"><label for="in_concepto" class="form-label">Concepto <span class="text-danger">*</span></label><select id="in_concepto" name="concepto" class="form-select form-select-sm" required><option value="">Seleccione...</option><option value="Inscripción">Inscripción</option><option value="Reinscripción">Reinscripción</option><option value="Titulación">Titulación</option><option value="Colegiatura">Colegiatura</option><option value="Constancia simple">Constancia simple</option><option value="Constancia con calificaciones">Constancia con calificaciones</option><option value="Historiales">Historiales</option><option value="Certificados">Certificados</option><option value="Equivalencias">Equivalencias</option><option value="Credenciales">Credenciales</option><option value="Otros">Otros</option></select></div>
                            <div class="col-md-3"><label for="in_mes_correspondiente" class="form-label">Mes Corresp.</label><input id="in_mes_correspondiente" name="mes_correspondiente" type="text" class="form-control form-control-sm" placeholder="Ej: Octubre"></div>
                            
                            <div class="col-md-3">
                                <label for="in_anio" class="form-label">Año <span class="text-danger">*</span></label>
                                <input id="in_anio" name="anio" type="number" min="2000" max="2100" class="form-control form-control-sm" placeholder="Ej: 2025" required>
                            </div>
                            
                            <div class="col-md-4"><label for="in_dia_pago" class="form-label">Día Pago</label><input id="in_dia_pago" name="dia_pago" type="number" min="1" max="31" class="form-control form-control-sm" placeholder="Ej: 15"></div>
                            <div class="col-12"><label for="in_observaciones" class="form-label">Observaciones</label><textarea id="in_observaciones" name="observaciones" class="form-control form-control-sm" rows="2"></textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger btn-sm">Guardar Ingreso</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content"><form id="formCategoria"><div class="modal-header modal-header-danger"><h5 class="modal-title" id="modalCategoriaTitle">Agregar/Editar Categoría</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><input type="hidden" id="categoria_id" name="id"><div class="mb-3"><label for="cat_nombre" class="form-label">Nombre de Categoría</label><input id="cat_nombre" name="nombre" type="text" class="form-control" required></div><div class="mb-3"><label for="cat_tipo" class="form-label">Tipo de Flujo</label><select id="cat_tipo" name="tipo" class="form-select" required><option value="Ingreso">Ingreso</option><option value="Egreso">Egreso</option></select></div><div class="mb-3"><label for="cat_descripcion" class="form-label">Descripción</label><textarea id="cat_descripcion" name="descripcion" class="form-control" rows="2"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger">Guardar Categoría</button></div></form></div>
        </div>
    </div>

    <div class="modal fade" id="modalPresupuesto" tabindex="-1" aria-labelledby="modalPresupuestoLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">
                <form id="formPresupuesto">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title" id="modalPresupuestoTitle">Asignar/Actualizar Presupuesto</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="presupuesto_id" name="id">
                        <div class="mb-3">
                            <label for="pres_tipo" class="form-label">Tipo de Presupuesto</label>
                            <select id="pres_tipo" name="tipo" class="form-select">
                                <option value="general">General</option>
                                <option value="categoria">Por Categoría</option>
                            </select>
                        </div>

                        <div class="mb-3" id="div_parent_presupuesto" style="display:none;">
                            <label for="pres_parent" class="form-label">Presupuesto General (padre)</label>
                            <select id="pres_parent" name="parent_presupuesto" class="form-select">
                                <option value="">Cargando presupuestos generales...</option>
                            </select>
                        </div>

                        <div class="mb-3" id="div_categoria">
                            <label for="pres_categoria" class="form-label">Categoría</label>
                            <select id="pres_categoria" name="id_categoria" class="form-select"></select>
                            <div class="form-text">Si selecciona "General", deje la categoría en blanco.</div>
                        </div>

                        <div class="mb-3">
                            <label for="pres_monto" class="form-label">Monto Límite</label>
                            <input id="pres_monto" name="monto" type="number" step="0.01" class="form-control" min="0.01" placeholder="Ej: 150000.00" required>
                        </div>
                        <div class="mb-3">
                            <label for="pres_fecha" class="form-label">Fecha de Asignación</label>
                            <input id="pres_fecha" name="fecha" type="date" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Guardar/Actualizar Presupuesto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Presupuesto por Categoría (separado para evitar problemas de populación) -->
    <div class="modal fade" id="modalPresupuestoCategoria" tabindex="-1" aria-labelledby="modalPresupuestoCategoriaLabel" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">
                <form id="formPresupuestoCategoria">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title" id="modalPresupuestoCategoriaTitle">Asignar Presupuesto por Categoría</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="presupuesto_categoria_id" name="id">

                        <div class="mb-3">
                            <label for="pres_parent_categoria" class="form-label">Presupuesto General (padre)</label>
                            <select id="pres_parent_categoria" name="parent_presupuesto" class="form-select">
                                <option value="">Cargando presupuestos generales...</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="pres_categoria_categoria" class="form-label">Categoría</label>
                            <select id="pres_categoria_categoria" name="id_categoria" class="form-select"></select>
                        </div>

                        <div class="mb-3">
                            <label for="pres_monto_categoria" class="form-label">Monto Límite</label>
                            <input id="pres_monto_categoria" name="monto" type="number" step="0.01" class="form-control" min="0.01" placeholder="Ej: 150000.00" required>
                        </div>
                        <div class="mb-3">
                            <label for="pres_fecha_categoria" class="form-label">Fecha de Asignación</label>
                            <input id="pres_fecha_categoria" name="fecha" type="date" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <!-- Cambiado a type=button para evitar envío nativo si JS falla -->
                        <button type="button" id="btnGuardarPresCategoria" class="btn btn-danger">Guardar Presupuesto por Categoría</button>
                        <button type="button" id="btnVerRawPresCat" class="btn btn-outline-secondary ms-2">Ver respuesta cruda</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Detalle de Auditoría -->
    <div class="modal fade" id="modalAuditoriaDetalle" tabindex="-1" aria-labelledby="modalAuditoriaDetalleLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-fullscreen-sm-down">
            <div class="modal-content">
                <div class="modal-header modal-header-danger">
                    <h5 class="modal-title" id="modalAuditoriaDetalleLabel">Detalle de Auditoría</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="aud_detalle_body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <small class="text-muted">Fecha</small>
                            <p class="mb-0" id="aud_det_fecha">-</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Usuario</small>
                            <p class="mb-0" id="aud_det_usuario">-</p>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted">Sección</small>
                            <p class="mb-0" id="aud_det_seccion">-</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted">Acción</small>
                        <p class="mb-0" id="aud_det_accion">-</p>
                    </div>
                    <hr>
                    <!-- Comparación lado a lado (para actualizaciones) -->
                    <div id="aud_compare_container" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Valores Anteriores</h6>
                                <div id="aud_old_values" class="border p-2 rounded bg-light" style="max-height: 300px; overflow-y: auto;"></div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Valores Nuevos</h6>
                                <div id="aud_new_values" class="border p-2 rounded bg-light" style="max-height: 300px; overflow-y: auto;"></div>
                            </div>
                        </div>
                    </div>
                    <!-- Detalles simples (para inserciones/eliminaciones) -->
                    <div id="aud_det_detalles_container" style="display: none;">
                        <h6 class="text-muted">Detalles</h6>
                        <div id="aud_det_detalles" class="border p-2 rounded bg-light" style="max-height: 400px; overflow-y: auto;"></div>
                    </div>
                    <hr>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="aud_toggle_raw">
                        <ion-icon name="code-outline"></ion-icon> Ver datos técnicos (JSON)
                    </button>
                    <pre id="aud_raw_consulta" class="mt-2" style="display: none;"></pre>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Desarrolladores (Easter Egg) -->
    <div class="modal fade" id="modalDesarrolladores" tabindex="-1" aria-labelledby="modalDesarrolladoresLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="border-radius: 15px; border: 3px solid #B80000;">
                <div class="modal-header" style="background: linear-gradient(135deg, #B80000 0%, #8b1d2b 100%); color: white; border-radius: 12px 12px 0 0;">
                    <h5 class="modal-title" id="modalDesarrolladoresLabel">
                        <ion-icon name="code-slash-outline" style="vertical-align: middle; font-size: 1.5rem;"></ion-icon>
                        Desarrolladores del Proyecto
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="background: #f8f9fa; padding: 2rem;">
                    <div class="text-center mb-4">
                        <h4 class="text-danger fw-bold">Sistema ERP - IUM</h4>
                        <p class="text-muted">Desarrollado con 💻 y ☕</p>
                    </div>
                    <div class="row g-4">
                        <!-- Desarrollador 1: Angel -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card shadow-sm h-100" style="border-radius: 15px; overflow: hidden; border: 2px solid #e0e0e0; transition: transform 0.3s;">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <img src="<?php echo BASE_URL; ?>public/images/angel.jpg" alt="Angel" 
                                             style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #B80000; box-shadow: 0 4px 8px rgba(0,0,0,0.2);"
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22%3E%3Crect fill=%22%23B80000%22 width=%22120%22 height=%22120%22/%3E%3Ctext fill=%22white%22 font-size=%2250%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EA%3C/text%3E%3C/svg%3E'">
                                    </div>
                                    <h5 class="fw-bold text-dark">Angel</h5>
                                    <p class="text-muted mb-2 small">Frontend Developer</p>
                                    <a href="https://instagram.com/villa_a607" target="_blank" class="text-decoration-none" style="color: #E1306C;">
                                        <ion-icon name="logo-instagram" style="vertical-align: middle; font-size: 1.2rem;"></ion-icon>
                                        <small>@villa_a607</small>
                                    </a>
                                    <div class="mt-3">
                                        <span class="badge bg-primary">JavaScript</span>
                                        <span class="badge bg-secondary">Bootstrap</span>
                                        <span class="badge bg-success">UI/UX</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Desarrollador 2: Boris -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card shadow-sm h-100" style="border-radius: 15px; overflow: hidden; border: 2px solid #e0e0e0; transition: transform 0.3s;">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <img src="<?php echo BASE_URL; ?>public/images/boris.jpg" alt="Boris" 
                                             style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #B80000; box-shadow: 0 4px 8px rgba(0,0,0,0.2);"
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22%3E%3Crect fill=%22%23B80000%22 width=%22120%22 height=%22120%22/%3E%3Ctext fill=%22white%22 font-size=%2250%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EB%3C/text%3E%3C/svg%3E'">
                                    </div>
                                    <h5 class="fw-bold text-dark">Boris</h5>
                                    <p class="text-muted mb-2 small">Full Stack Developer</p>
                                    <a href="https://instagram.com/f_jimen9z" target="_blank" class="text-decoration-none" style="color: #E1306C;">
                                        <ion-icon name="logo-instagram" style="vertical-align: middle; font-size: 1.2rem;"></ion-icon>
                                        <small>@f_jimen9z</small>
                                    </a>
                                    <div class="mt-3">
                                        <span class="badge bg-danger">PHP</span>
                                        <span class="badge bg-primary">JavaScript</span>
                                        <span class="badge bg-success">MySQL</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Desarrollador 3: Chris -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card shadow-sm h-100" style="border-radius: 15px; overflow: hidden; border: 2px solid #e0e0e0; transition: transform 0.3s;">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <img src="<?php echo BASE_URL; ?>public/images/chris.jpg" alt="Chris" 
                                             style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #B80000; box-shadow: 0 4px 8px rgba(0,0,0,0.2);"
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22%3E%3Crect fill=%22%23B80000%22 width=%22120%22 height=%22120%22/%3E%3Ctext fill=%22white%22 font-size=%2250%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3EC%3C/text%3E%3C/svg%3E'">
                                    </div>
                                    <h5 class="fw-bold text-dark">Chris</h5>
                                    <p class="text-muted mb-2 small">Apoyo Moral 😄</p>
                                    <a href="https://instagram.com/ricardo_.gmzx" target="_blank" class="text-decoration-none" style="color: #E1306C;">
                                        <ion-icon name="logo-instagram" style="vertical-align: middle; font-size: 1.2rem;"></ion-icon>
                                        <small>@ricardo_.gmzx</small>
                                    </a>
                                    <div class="mt-3">
                                        <span class="badge bg-warning text-dark">Risas</span>
                                        <span class="badge bg-info">Motivación</span>
                                        <span class="badge" style="background-color: #ff69b4;">Buen Humor</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Desarrollador 4: Doctor -->
                        <div class="col-md-6 col-lg-3">
                            <div class="card shadow-sm h-100" style="border-radius: 15px; overflow: hidden; border: 2px solid #e0e0e0; transition: transform 0.3s;">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <img src="<?php echo BASE_URL; ?>public/images/doctor.jpg" alt="Doctor" 
                                             style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #B80000; box-shadow: 0 4px 8px rgba(0,0,0,0.2);"
                                             onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22120%22 height=%22120%22%3E%3Crect fill=%22%23B80000%22 width=%22120%22 height=%22120%22/%3E%3Ctext fill=%22white%22 font-size=%2250%22 x=%2250%25%22 y=%2250%25%22 text-anchor=%22middle%22 dy=%22.3em%22%3ED%3C/text%3E%3C/svg%3E'">
                                    </div>
                                    <h5 class="fw-bold text-dark">Doctor</h5>
                                    <p class="text-muted mb-2 small">Backend Specialist</p>
                                    <a href="https://instagram.com/mane.jandro1002" target="_blank" class="text-decoration-none" style="color: #E1306C;">
                                        <ion-icon name="logo-instagram" style="vertical-align: middle; font-size: 1.2rem;"></ion-icon>
                                        <small>@mane.jandro1002</small>
                                    </a>
                                    <div class="mt-3">
                                        <span class="badge bg-danger">PHP</span>
                                        <span class="badge bg-info">Database</span>
                                        <span class="badge bg-warning text-dark">API</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <p class="text-muted small mb-0">
                            <ion-icon name="heart" style="color: #B80000; vertical-align: middle;"></ion-icon>
                            Creado para Instituto Universitario Morelia - 2025
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const CURRENT_USER = <?php echo json_encode($currentUser); ?>;
    </script>
    <script>
        // Easter Egg: 5 clics en el logo para ver desarrolladores
        (function() {
            let clickCount = 0;
            let resetTimer = null;
            const logoContainer = document.getElementById('logoContainer');
            
            if (logoContainer) {
                logoContainer.addEventListener('click', function() {
                    clickCount++;
                    
                    // Visual feedback
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => { this.style.transform = 'scale(1)'; }, 100);
                    
                    // Reset counter after 2 seconds of inactivity
                    clearTimeout(resetTimer);
                    resetTimer = setTimeout(() => { clickCount = 0; }, 2000);
                    
                    // Show modal on 5th click
                    if (clickCount === 5) {
                        clickCount = 0;
                        
                        // Cerrar sidebar si está abierto en móvil
                        const sidebar = document.getElementById('sidebar');
                        const sidebarOverlay = document.getElementById('sidebarOverlay');
                        if (sidebar && sidebar.classList.contains('open')) {
                            sidebar.classList.remove('open');
                            sidebar.classList.add('closed');
                            if (sidebarOverlay) {
                                sidebarOverlay.style.display = 'none';
                            }
                            document.body.style.overflow = '';
                        }
                        
                        // Mostrar modal de desarrolladores
                        const modal = new bootstrap.Modal(document.getElementById('modalDesarrolladores'));
                        modal.show();
                        
                        // Easter egg sound effect (optional)
                        try {
                            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuBzvLaiTcIGWi77eefTRAMUKfj8LZjHAY4ktfyynksheB');
                        } catch(e) {}
                    }
                });
                
                // Smooth transition
                logoContainer.style.transition = 'transform 0.1s ease';
            }
        })();
        
        // Responsive enhancements: toggle sidebar on small screens and make modals fullscreen-sm-down
        (function(){
            const sidebar = document.getElementById('sidebar');
            const btn = document.getElementById('btnToggleSidebar');
            const overlay = document.getElementById('sidebarOverlay');
            function openSidebar() { sidebar.classList.remove('closed'); sidebar.classList.add('open'); overlay.style.display = 'block'; document.body.style.overflow = 'hidden'; }
            function closeSidebar() { sidebar.classList.remove('open'); sidebar.classList.add('closed'); overlay.style.display = 'none'; document.body.style.overflow = ''; }
            // Initialize closed on small screens
            function adaptOnResize() {
                if (window.innerWidth < 992) { sidebar.classList.add('closed'); sidebar.classList.remove('open'); document.getElementById('main-content').style.marginLeft = '0'; }
                else { sidebar.classList.remove('closed'); sidebar.classList.remove('open'); overlay.style.display = 'none'; document.getElementById('main-content').style.marginLeft = '280px'; }
            }
            if (btn) btn.addEventListener('click', function(){ if (sidebar.classList.contains('open')) closeSidebar(); else openSidebar(); });
            if (overlay) overlay.addEventListener('click', closeSidebar);
            window.addEventListener('resize', adaptOnResize);
            adaptOnResize();

            // Add Bootstrap modal fullscreen on small devices for better UX
            try {
                document.querySelectorAll('.modal-dialog').forEach(function(md){ md.classList.add('modal-fullscreen-sm-down'); });
            } catch(e) { console.warn('No pude agregar modal-fullscreen-sm-down', e); }
        })();
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js para gráficas -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
</body>
</html>