<?php
// ===============================================
// Archivo: vistas/egresos.php
// Función: Contenido del módulo Egresos.
// ===============================================
?>
<nav id="action-menu-container" class="action-menu">
    <button class="btn btn-sm btn-light text-secondary me-3 active" data-action="historial-egresos"><ion-icon name="list-outline"></ion-icon> Historial</button>
    <button class="btn btn-sm btn-light text-secondary me-3" data-bs-toggle="modal" data-bs-target="#modalAgregarEgreso"><ion-icon name="add-circle-outline"></ion-icon> Agregar Egreso</button>
    <button class="btn btn-sm btn-light text-secondary me-3" data-action="reportes-egresos" onclick="showReports('Egresos')"><ion-icon name="document-text-outline"></ion-icon> Reportes</button>
</nav>

<div id="view-container" class="p-4">
    <h2 class="mb-4 text-danger">Egresos: Historial Reciente</h2>
    <div id="egresos-view-content">
        <div class="card shadow-sm">
            <div class="card-header bg-light"><h5 class="mb-0">Movimientos Recientes</h5></div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead><tr><th>Fecha</th><th>Concepto</th><th>Monto</th><th>Categoría</th></tr></thead>
                    <tbody>
                        <tr><td>2025-09-30</td><td>Pago nómina Septiembre</td><td class="text-danger fw-bold">$ 80,000.00</td><td>Nómina</td></tr>
                        <tr><td>2025-09-28</td><td>Servicio de Internet</td><td class="text-danger fw-bold">$ 1,500.00</td><td>Servicios Básicos</td></tr>
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
                    <tr><td>RPT-EGR-001</td><td>Egresos</td><td>2025-10-01</td><td><button class="btn btn-sm btn-outline-danger">PDF</button> <button class="btn btn-sm btn-outline-success">Excel</button></td></tr>
                    <tr><td>RPT-EGR-002</td><td>Egresos</td><td>2025-10-02</td><td><button class="btn btn-sm btn-outline-danger">PDF</button> <button class="btn btn-sm btn-outline-success">Excel</button></td></tr>
                </tbody>
            </table>
        `;
        $('.action-menu button').removeClass('active');
        $('[data-action="reportes-egresos"]').addClass('active');
    }
</script>