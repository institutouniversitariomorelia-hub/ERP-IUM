<?php
// views/layout.php

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
        #sidebar { width: 250px; height: 100vh; position: fixed; background-color: var(--c-primario); background-image: linear-gradient(180deg, #c50000 0%, #a00000 100%); padding: 0; z-index: 1000; box-shadow: 0 0 20px rgba(0,0,0,0.2); display: flex; flex-direction: column; }
        #sidebar .logo-container { padding: 1rem; text-align: center; border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        #sidebar .logo-container img { max-height: 55px; margin-bottom: 10px; }
        #sidebar .nav { flex-grow: 1; }
        #sidebar .nav-link { color: rgba(255, 255, 255, 0.8); padding: 12px 20px; border-left: 4px solid transparent; transition: all 0.2s ease-in-out; font-size: 0.95rem; cursor: pointer; display: flex; align-items: center; }
        #sidebar .nav-link ion-icon { font-size: 1.2rem; vertical-align: middle; margin-right: 10px; min-width: 20px; }
        #sidebar .nav-link:hover { background-color: var(--c-primario-hover); color: var(--c-blanco); border-left-color: var(--c-blanco); }
        #sidebar .nav-link.active { background-color: var(--c-primario-hover); border-left-color: var(--c-blanco); color: var(--c-blanco); font-weight: bold; }
        #sidebar .nav-item.mt-auto { margin-top: auto; border-top: 1px solid rgba(255, 255, 255, 0.1); }
        #main-content { margin-left: 250px; width: calc(100% - 250px); }
        .top-header { background-color: var(--c-blanco); color: var(--c-texto-principal); padding: 10px 30px; font-size: 1.1rem; border-bottom: 1px solid #dee2e6; box-shadow: var(--sombra-suave); position: sticky; top: 0; z-index: 999; display: flex; justify-content: space-between; align-items: center; }
        #view-container { padding: 30px; }
        .card { border: none; border-radius: 8px; box-shadow: var(--sombra-suave); margin-bottom: 1.5rem; }
        .card-header { background-color: var(--c-blanco); font-weight: bold; border-bottom: 1px solid #f0f0f0; padding: 1rem 1.25rem; }
        .table { border-collapse: separate; border-spacing: 0; }
        .table thead th { background-color: #f8f9fa; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 0.5px; color: var(--c-texto-secundario); border-bottom-width: 1px; padding: 0.75rem; vertical-align: bottom;}
        .table tbody tr:hover { background-color: #f1f1f1; }
        .table td, .table th { padding: 0.75rem; vertical-align: middle; }
        .btn { border-radius: 6px; transition: all 0.2s ease-in-out; padding: 0.375rem 0.75rem; font-size: 0.9rem;}
        .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.8rem; }
        .btn-danger { background-color: var(--c-primario); border-color: var(--c-primario); }
        .btn-danger:hover { background-color: var(--c-primario-hover); border-color: var(--c-primario-hover); transform: translateY(-1px); box-shadow: var(--sombra-suave);}
        .modal-content { border: none; border-radius: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .modal-header-danger { background-color: var(--c-primario); color: white; border-top-left-radius: 7px; border-top-right-radius: 7px;}
        .modal-title { font-weight: 500; }
        .badge { padding: 0.4em 0.6em; }
    </style>
</head>
<body>
    <div id="sidebar">
        <div class="logo-container">
            <img src="<?php echo BASE_URL; ?>public/logo ium blanco.png" alt="Logo IUM Blanco" class="img-fluid">
            <p class="text-white small mt-1 mb-0 opacity-75">Navegación Principal</p>
        </div>
        <ul class="nav flex-column">
             <?php $activeModule = $activeModule ?? ''; ?>
            <li class="nav-item"><a class="nav-link <?php echo $activeModule === 'profile' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php?controller=user&action=profile"><ion-icon name="person-circle-outline"></ion-icon> Mi Perfil</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeModule === 'egresos' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php?controller=egreso&action=index"><ion-icon name="trending-down-outline"></ion-icon> Egresos</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeModule === 'ingresos' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php?controller=ingreso&action=index"><ion-icon name="trending-up-outline"></ion-icon> Ingresos</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeModule === 'categorias' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php?controller=categoria&action=index"><ion-icon name="pricetags-outline"></ion-icon> Categorías</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeModule === 'presupuestos' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php?controller=presupuesto&action=index"><ion-icon name="wallet-outline"></ion-icon> Presupuestos</a></li>
            <li class="nav-item"><a class="nav-link <?php echo $activeModule === 'auditoria' ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>index.php?controller=auditoria&action=index"><ion-icon name="layers-outline"></ion-icon> Historial Auditoría</a></li>
        </ul>
        <ul class="nav flex-column mt-auto">
             <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>index.php?controller=auth&action=logout"><ion-icon name="log-out-outline"></ion-icon> Cerrar Sesión</a></li>
        </ul>
    </div>

    <div id="main-content">
        <header class="top-header">
            <span>SISTEMA DE CONTROL DE EGRESOS Y INGRESOS IUM</span>
            <span class="fs-6 fw-normal">Usuario: <strong><?php echo htmlspecialchars($currentUser['nombre']); ?></strong></span>
        </header>

        <main id="view-container" class="p-4">
            <?php
            if (isset($content)) {
                echo $content;
            } else {
                echo '<div class="alert alert-danger">Error: Contenido de la vista no encontrado.</div>';
            }
            ?>
        </main>
    </div>

    <div class="modal fade" id="modalUsuario" tabindex="-1" aria-labelledby="modalUsuarioLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formUsuario">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title" id="modalUsuarioTitle">Registrar/Editar Usuario</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="usuario_id" name="id">
                        <div class="mb-3"><label for="usuario_nombre" class="form-label">Nombre</label><input id="usuario_nombre" name="nombre" type="text" class="form-control" required></div>
                        <div class="mb-3"><label for="usuario_username" class="form-label">Usuario (username)</label><input id="usuario_username" name="username" type="text" class="form-control" required></div>
                        <div class="mb-3"><label for="usuario_password" class="form-label">Contraseña</label><input id="usuario_password" name="password" type="password" class="form-control"><div class="form-text">Dejar en blanco para no cambiar. Obligatorio para usuarios nuevos.</div></div>
                        <div class="mb-3"><label for="usuario_rol" class="form-label">Asignar Rol</label><select id="usuario_rol" name="rol" class="form-select" required><option value="ADM">Administración (ADM)</option><option value="COB">Cobranzas (COB)</option><option value="REC">Rectoría (REC)</option><option value="SU">SUPER USUARIO (SU)</option></select></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger">Guardar Usuario</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalCambiarPassword" tabindex="-1" aria-labelledby="modalCambiarPasswordLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formCambiarPassword">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title" id="modalCambiarPasswordLabel">Cambiar Contraseña</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="change_pass_username" name="username">
                        <div class="mb-3"><label class="form-label">Nueva Contraseña para <strong id="username_display"></strong></label><input id="change_pass_password" name="password" type="password" class="form-control" required></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger">Guardar Contraseña</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalEgreso" tabindex="-1" aria-labelledby="modalEgresoLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formEgreso">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title" id="modalEgresoTitle">Registrar/Editar Egreso</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
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
                                <select id="eg_id_categoria" name="id_categoria" class="form-select form-select-sm" required>
                                    </select>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formIngreso">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title" id="modalIngresoTitle">Registrar/Editar Ingreso</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="ingreso_id" name="id">

                        <div class="row g-3">
                            
                            <div class="col-md-4">
                                <label for="in_fecha" class="form-label">Fecha Pago <span class="text-danger">*</span></label>
                                <input id="in_fecha" name="fecha" type="date" class="form-control form-control-sm" required>
                            </div>
                            <div class="col-md-4">
                                <label for="in_monto" class="form-label">Monto <span class="text-danger">*</span></label>
                                <input id="in_monto" name="monto" type="number" step="0.01" class="form-control form-control-sm" min="0.01" placeholder="Ej: 5000.00" required>
                            </div>
                            <div class="col-md-4">
                                <label for="in_metodo_de_pago" class="form-label">Método Pago <span class="text-danger">*</span></label>
                                <select id="in_metodo_de_pago" name="metodo_de_pago" class="form-select form-select-sm" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Depósito">Depósito</option>
                                </select>
                            </div>

                            
                             <div class="col-md-8">
                                <label for="in_alumno" class="form-label">Alumno <span class="text-danger">*</span></label>
                                <input id="in_alumno" name="alumno" type="text" class="form-control form-control-sm" placeholder="Nombre completo del alumno" required>
                            </div>
                             <div class="col-md-4">
                                <label for="in_matricula" class="form-label">Matrícula <span class="text-danger">*</span></label>
                                <input id="in_matricula" name="matricula" type="text" class="form-control form-control-sm" required>
                            </div>

                             
                            <div class="col-md-4">
                                <label for="in_nivel" class="form-label">Nivel <span class="text-danger">*</span></label>
                                <select id="in_nivel" name="nivel" class="form-select form-select-sm" required>
                                    <option value="">Seleccione...</option>
                                    <option value="Licenciatura">Licenciatura</option>
                                    <option value="Maestría">Maestría</option>
                                    <option value="Doctorado">Doctorado</option>
                                </select>
                            </div>
                             <div class="col-md-5">
                                <label for="in_programa" class="form-label">Programa <span class="text-danger">*</span></label>
                                <input id="in_programa" name="programa" type="text" class="form-control form-control-sm" placeholder="Ej: Lic. en Derecho" required>
                            </div>
                             <div class="col-md-3">
                                <label for="in_grado" class="form-label">Grado</label>
                                <input id="in_grado" name="grado" type="number" min="1" max="15" class="form-control form-control-sm" placeholder="Ej: 1, 5">
                            </div>
                             <div class="col-md-4">
                                <label for="in_modalidad" class="form-label">Modalidad</label>
                                <select id="in_modalidad" name="modalidad" class="form-select form-select-sm">
                                    <option value="">N/A</option>
                                    <option value="Cuatrimestral">Cuatrimestral</option>
                                    <option value="Semestral">Semestral</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="in_grupo" class="form-label">Grupo</label>
                                <input id="in_grupo" name="grupo" type="text" class="form-control form-control-sm" placeholder="Ej: A, 101">
                            </div>

                            
                             <div class="col-md-4">
                                 <label for="in_id_categoria" class="form-label">Categoría <span class="text-danger">*</span></label>
                                <select id="in_id_categoria" name="id_categoria" class="form-select form-select-sm" required>
                                    </select>
                            </div>
                             <div class="col-md-6">
                                <label for="in_concepto" class="form-label">Concepto <span class="text-danger">*</span></label>
                                <select id="in_concepto" name="concepto" class="form-select form-select-sm" required>
                                     <option value="">Seleccione...</option>
                                     <option value="Inscripción">Inscripción</option>
                                     <option value="Reinscripción">Reinscripción</option>
                                     <option value="Titulación">Titulación</option>
                                     <option value="Colegiatura">Colegiatura</option>
                                     <option value="Constancia simple">Constancia simple</option>
                                     <option value="Constancia con calificaciones">Constancia con calificaciones</option>
                                     <option value="Historiales">Historiales</option>
                                     <option value="Certificados">Certificados</option>
                                     <option value="Equivalencias">Equivalencias</option>
                                     <option value="Credenciales">Credenciales</option>
                                     <option value="Otros">Otros</option>
                                </select>
                            </div>
                             <div class="col-md-3">
                                <label for="in_mes_correspondiente" class="form-label">Mes Corresp.</label>
                                <input id="in_mes_correspondiente" name="mes_correspondiente" type="text" class="form-control form-control-sm" placeholder="Ej: Octubre">
                            </div>
                            <div class="col-md-3">
                                <label for="in_año" class="form-label">Año <span class="text-danger">*</span></label>
                                <input id="in_año" name="año" type="number" min="2000" max="2100" class="form-control form-control-sm" placeholder="Ej: 2025" required>
                            </div>
                             <div class="col-md-4 d-none"> {/* Ocultamos folio por ahora */}
                                <label for="in_folio" class="form-label">Folio Ref.</label>
                                <input id="in_folio" name="folio" type="text" class="form-control form-control-sm" placeholder="Referencia interna">
                            </div>
                            <div class="col-md-4">
                                <label for="in_dia_pago" class="form-label">Día Pago</label>
                                <input id="in_dia_pago" name="dia_pago" type="number" min="1" max="31" class="form-control form-control-sm" placeholder="Ej: 15">
                            </div>

                        
                            <div class="col-12">
                                <label for="in_observaciones" class="form-label">Observaciones</label>
                                <textarea id="in_observaciones" name="observaciones" class="form-control form-control-sm" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger btn-sm">Guardar Ingreso</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    

    <div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formCategoria">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title" id="modalCategoriaTitle">Agregar/Editar Categoría</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="categoria_id" name="id">
                        <div class="mb-3"><label for="cat_nombre" class="form-label">Nombre de Categoría</label><input id="cat_nombre" name="nombre" type="text" class="form-control" required></div>
                        <div class="mb-3"><label for="cat_tipo" class="form-label">Tipo de Flujo</label><select id="cat_tipo" name="tipo" class="form-select" required><option value="Ingreso">Ingreso</option><option value="Egreso">Egreso</option></select></div>
                        <div class="mb-3"><label for="cat_descripcion" class="form-label">Descripción</label><textarea id="cat_descripcion" name="descripcion" class="form-control" rows="2"></textarea></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger">Guardar Categoría</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPresupuesto" tabindex="-1" aria-labelledby="modalPresupuestoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formPresupuesto">
                    <div class="modal-header modal-header-danger">
                        <h5 class="modal-title" id="modalPresupuestoTitle">Asignar/Actualizar Presupuesto</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="presupuesto_id" name="id">
                        <div class="mb-3"><label for="pres_categoria" class="form-label">Categoría</label><select id="pres_categoria" name="categoria" class="form-select" required></select></div>
                        <div class="mb-3"><label for="pres_monto" class="form-label">Monto Límite</label><input id="pres_monto" name="monto" type="number" step="0.01" class="form-control" min="0.01" placeholder="Ej: 150000.00" required></div>
                        <div class="mb-3"><label for="pres_fecha" class="form-label">Fecha de Asignación</label><input id="pres_fecha" name="fecha" type="date" class="form-control" required></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-danger">Guardar/Actualizar Presupuesto</button></div>
                </form>
            </div>
        </div>
    </div>

    <script>
        const BASE_URL = '<?php echo BASE_URL; ?>';
        const CURRENT_USER = <?php echo json_encode($currentUser); ?>;
    </script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo BASE_URL; ?>public/js/app.js"></script>
</body>
</html>