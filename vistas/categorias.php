<?php
// ===============================================
// Archivo: vistas/categorias.php
// Función: Contenido del módulo Categorías.
// ===============================================
?>
<nav id="action-menu-container" class="action-menu">
    <button class="btn btn-sm btn-light text-secondary me-3 active" data-action="vista-categorias"><ion-icon name="list-outline"></ion-icon> Ver Categorías</button>
    <button class="btn btn-sm btn-light text-secondary me-3" data-bs-toggle="modal" data-bs-target="#modalAgregarCategoria"><ion-icon name="add-circle-outline"></ion-icon> Agregar Categoría</button>
    <button class="btn btn-sm btn-light text-secondary me-3" data-action="eliminar-categoria"><ion-icon name="trash-outline"></ion-icon> Eliminar</button>
</nav>

<div id="view-container" class="p-4">
    <h2 class="mb-4 text-danger">Categorías: Catálogo Actual</h2>
    <div id="categorias-view-content">
        <div class="card shadow-sm">
            <div class="card-header bg-light"><h5 class="mb-0">Clasificaciones de Flujo</h5></div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead><tr><th>ID</th><th>Nombre</th><th>Tipo de Flujo</th><th>Acciones</th></tr></thead>
                    <tbody>
                        <tr><td>I-1</td><td>Colegiaturas</td><td class="text-success fw-bold">Ingreso</td><td><button class="btn btn-sm btn-warning">Editar</button></td></tr>
                        <tr><td>E-1</td><td>Nómina</td><td class="text-danger fw-bold">Egreso</td><td><button class="btn btn-sm btn-warning">Editar</button></td></tr>
                        <tr><td>E-2</td><td>Servicios Básicos</td><td class="text-danger fw-bold">Egreso</td><td><button class="btn btn-sm btn-warning">Editar</button></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>