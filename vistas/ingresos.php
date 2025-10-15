<?php
// ===============================================
// Archivo: vistas/ingresos.php
// Función: Contenido del módulo Ingresos.
// ===============================================
?>
<nav id="action-menu-container" class="action-menu">
    <button class="btn btn-sm btn-light text-secondary me-3 active" data-action="historial-ingresos"><ion-icon name="list-outline"></ion-icon> Historial</button>
    <button class="btn btn-sm btn-light text-secondary me-3" data-bs-toggle="modal" data-bs-target="#modalAgregarIngreso"><ion-icon name="add-circle-outline"></ion-icon> Agregar Ingreso</button>
    <button class="btn btn-sm btn-light text-secondary me-3" data-action="reportes-ingresos" onclick="showReports('Ingresos')"><ion-icon name="document-text-outline"></ion-icon> Reportes</button>
</nav>

<div id="view-container" class="p-4">
    <h2 class="mb-4 text-danger">Ingresos: Historial Reciente</h2>
    <div id="ingresos-view-content">
        <div class="card shadow-sm">
            <div class="card-header bg-light"><h5 class="mb-0">Ingresos Recientes</h5></div>
            <div class="card-body">
                <table class="table table-striped table-hover">
                    <thead><tr><th>Fecha</th><th>Referencia</th><th>Monto</th><th>Categoría</th></tr></thead>
                    <tbody>
                        <tr><td>2025-09-30</td><td>PAGO-UM1002</td><td class="text-success fw-bold">$ 5,500.00</td><td>Colegiaturas</td></tr>
                        <tr><td>2025-09-29</td><td>PAGO-UM1050</td><td class="text-success fw-bold">$ 1,200.00</td><td>Inscripciones</td></tr>
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
                    <tr><td>RPT-ING-001</td><td>Ingresos</td><td>2025-10-01</td><td><button class="btn btn-sm btn-outline-danger">PDF</button> <button class="btn btn-sm btn-outline-success">Excel</button></td></tr>
                    <tr><td>RPT-ING-002</td><td>Ingresos</td><td>2025-10-02</td><td><button class="btn btn-sm btn-outline-danger">PDF</button> <button class="btn btn-sm btn-outline-success">Excel</button></td></tr>
                </tbody>
            </table>
        `;
        $('.action-menu button').removeClass('active');
        $('[data-action="reportes-ingresos"]').addClass('active');
    }
</script>
