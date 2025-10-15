<?php
// ===============================================
// Archivo: vistas/auditoria.php
// Función: Contenido del módulo Historial Auditoría.
// ===============================================
?>
<nav id="action-menu-container" class="action-menu">
    <button class="btn btn-sm btn-light text-secondary me-3 active" data-action="historial-global"><ion-icon name="layers-outline"></ion-icon> Historial Global</button>
    <button class="btn btn-sm btn-light text-secondary me-3" data-action="historial-cambios"><ion-icon name="time-outline"></ion-icon> Historial de Cambios</button>
    <button class="btn btn-sm btn-light text-secondary me-3" data-action="generar-reporte-auditoria" onclick="showReports('Auditoría')"><ion-icon name="document-text-outline"></ion-icon> Generar Reporte</button>
</nav>

<div id="view-container" class="p-4">
    <h2 class="mb-4 text-danger">Historial de Auditoría: Reporte Global</h2>
    <div id="auditoria-view-content">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light"><h5 class="mb-0">Filtros de Consulta</h5></div>
            <div class="card-body">
                <form class="row g-3">
                    <div class="col-md-3"><select class="form-select"><option>Tabla Afectada</option></select></div>
                    <div class="col-md-3"><select class="form-select"><option>Usuario Afectante</option></select></div>
                    <div class="col-md-4 d-flex"><input type="date" class="form-control me-2"><input type="date" class="form-control"></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-danger w-100">Filtrar</button></div>
                </form>
            </div>
        </div>
        
        <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered table-striped table-hover">
                    <thead><tr><th>Fecha</th><th>Usuario</th><th>Tabla</th><th>Valor Anterior</th><th>Valor Nuevo</th></tr></thead>
                    <tbody>
                        <tr><td>2025-10-06</td><td>ADM_User</td><td>PRESUPUESTOS</td><td>$20,000</td><td class="text-danger">$30,000</td></tr>
                        <tr><td>2025-10-05</td><td>SU_Admin</td><td>USUARIOS</td><td>COB</td><td class="text-primary">ADM</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    function showReports(type) {
        // Simulación de carga de vista de reportes
        document.getElementById('view-container').innerHTML = `
            <h2 class="mb-4 text-danger">${type}: Generación de Reportes</h2>
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light"><h5 class="mb-0">Generar Documentos</h5></div>
                <div class="card-body text-center">
                    <button class="btn btn-danger me-4" data-bs-toggle="modal" data-bs-target="#modalReporte">
                        <ion-icon name="document-lock-outline"></ion-icon> Generar Reporte PDF
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalReporte">
                        <ion-icon name="analytics-outline"></ion-icon> Generar Reporte Excel
                    </button>
                </div>
            </div>
            <h4 class="mt-4 text-danger">Lista de Reportes Generados Recientemente</h4>
             <table class="table table-striped table-hover">
                <thead><tr><th>ID Reporte</th><th>Tipo</th><th>Fecha Generación</th><th>Descargar</th></tr></thead>
                <tbody>
                    <tr><td>RPT-AUD-001</td><td>Auditoría</td><td>2025-10-01</td><td><button class="btn btn-sm btn-outline-danger">PDF</button> <button class="btn btn-sm btn-outline-success">Excel</button></td></tr>
                    <tr><td>RPT-AUD-002</td><td>Auditoría</td><td>2025-10-02</td><td><button class="btn btn-sm btn-outline-danger">PDF</button> <button class="btn btn-sm btn-outline-success">Excel</button></td></tr>
                </tbody>
            </table>
        `;
        $('.action-menu button').removeClass('active');
        $('[data-action="generar-reporte-auditoria"]').addClass('active');
    }
</script>