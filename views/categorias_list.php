<?php
// views/categorias_list.php
// Llamada por CategoriaController->index()
// Variables disponibles: $pageTitle, $activeModule, $categorias, $currentUser (del layout)
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-danger mb-0"><?php echo htmlspecialchars($pageTitle); ?>: Catálogo Actual</h3>
    <div>
            <?php if (roleCan('add','categorias')): ?>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalCategoria" id="btnNuevaCategoria">
                    <ion-icon name="add-circle-outline" class="me-1"></ion-icon> Agregar Categoría
                </button>
            <?php endif; ?>
            <button class="btn btn-outline-secondary" id="btnRefrescarCategorias" title="Recargar lista">
                <ion-icon name="refresh-outline"></ion-icon> Refrescar
            </button>
    </div>
</div>

<div class="action-menu mb-4 d-none">
    <button class="btn active">Ver Categorías</button>
    <button class="btn" data-bs-toggle="modal" data-bs-target="#modalCategoria">Agregar Categoría</button>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr class="table-light">
                        <th class="col-id">ID</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaCategorias">
                    <?php if (empty($categorias)): ?>
                        <tr><td colspan="5" class="text-center p-4 text-muted">No hay categorías registradas.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categorias as $categoria):
                            $tipoBadge = $categoria['tipo'] === 'Ingreso'
                                ? '<span class="badge bg-success">Ingreso</span>'
                                : '<span class="badge bg-danger">Egreso</span>';
                        ?>
                            <tr>
                                <td><?php echo $categoria['id']; ?></td>
                                <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                                <td><?php echo $tipoBadge; ?></td>
                                <td><?php echo htmlspecialchars($categoria['descripcion'] ?: '-'); // Muestra '-' si está vacío ?></td>
                                <td class="text-center">
                                    <div class="btn-responsive-sm">
                                        <?php if (roleCan('edit','categorias')): ?>
                                            <button class="btn btn-sm btn-warning btn-edit-categoria"
                                                    data-id="<?php echo $categoria['id']; ?>"
                                                    data-bs-toggle="modal" data-bs-target="#modalCategoria"
                                                    title="Editar Categoría">
                                                 <ion-icon name="create-outline"></ion-icon> Editar
                                            </button>
                                        <?php endif; ?>
                                        <?php if (roleCan('delete','categorias')): ?>
                                            <button class="btn btn-sm btn-danger btn-del-categoria"
                                                    data-id="<?php echo $categoria['id']; ?>"
                                                    title="Eliminar Categoría">
                                                <ion-icon name="trash-outline"></ion-icon> Eliminar
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>