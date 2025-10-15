<?php
// ===============================================
// Archivo: vistas/presupuestos.php
// Función: Contenido del módulo Presupuestos.
// ===============================================
?>
<nav id="action-menu-container" class="action-menu">
    <button class="btn btn-sm btn-light text-secondary me-3 active" data-action="vista-presupuestos"><ion-icon name="analytics-outline"></ion-icon> Ver Presupuesto</button>
    <button class="btn btn-sm btn-light text-secondary me-3" data-bs-toggle="modal" data-bs-target="#modalAgregarPresupuesto" data-action="agregar-presupuesto"><ion-icon name="add-circle-outline"></ion-icon> Agregar</button>
    <button class="btn btn-sm btn-light text-secondary me-3" data-bs-toggle="modal" data-bs-target="#modalAgregarPresupuesto" data-action="actualizar-presupuesto"><ion-icon name="create-outline"></ion-icon> Actualizar</button>
</nav>

<div id="view-container" class="p-4">
    <h2 class="mb-4 text-danger">Presupuestos: Resumen General</h2>
    <div id="presupuestos-view-content">
        <div class="card bg-light shadow-sm mb-4">
            <div class="card-body">
                <h4 class="card-title text-danger">Presupuesto General Anual [2025]</h4>
                <div class="row text-center mt-4">
                    <div class="col-md-4"><p class="h5">Total</p><p class="h2 text-success">$ 500,000.00</p></div>
                    <div class="col-md-4"><p class="h5">Asignado</p><p class="h2 text-warning">$ 350,000.00</p></div>
                    <div class="col-md-4"><p class="h5">Disponible</p><p class="h2 text-primary">$ 150,000.00</p></div>
                </div>
            </div>
        </div>
        <h4 class="mt-4">Asignación por Categoría</h4>
         <div class="card shadow-sm">
            <div class="card-body">
                <table class="table table-bordered">
                    <thead><tr><th>Categoría</th><th>Monto Asignado</th><th>Gasto Actual</th><th>% Utilizado</th></tr></thead>
                    <tbody>
                         <tr><td>Nómina</td><td class="fw-bold">$ 200,000.00</td><td>$ 150,000.00</td><td><div class="progress"><div class="progress-bar bg-danger" style="width: 75%;">75%</div></div></td></tr>
                        <tr><td>Servicios Básicos</td><td class="fw-bold">$ 50,000.00</td><td>$ 48,000.00</td><td><div class="progress"><div class="progress-bar bg-danger" style="width: 96%;">96%</div></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>