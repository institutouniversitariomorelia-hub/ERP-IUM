<?php
// views/categorias_list.php
// Llamada por CategoriaController->index()
// Variables disponibles: $pageTitle, $activeModule, $categorias, $currentUser (del layout)
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
    <h3 class="text-danger mb-0"><?php echo htmlspecialchars($pageTitle); ?>: Catálogo Actual</h3>
    <div class="d-flex gap-2 w-100 w-md-auto">
            <?php if (roleCan('add','categorias')): ?>
                <button class="btn btn-danger flex-grow-1 flex-md-grow-0" data-bs-toggle="modal" data-bs-target="#modalCategoria" id="btnNuevaCategoria">
                    <ion-icon name="add-circle-outline" class="me-1"></ion-icon> 
                    <span class="d-none d-sm-inline">Agregar Categoría</span>
                    <span class="d-inline d-sm-none">Agregar</span>
                </button>
            <?php endif; ?>
    </div>
</div>

<div class="action-menu mb-4 d-none">
    <button class="btn active">Ver Categorías</button>
    <button class="btn" data-bs-toggle="modal" data-bs-target="#modalCategoria">Agregar Categoría</button>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <?php 
            // Verificar si el usuario tiene permisos para editar o eliminar
            $hasActions = roleCan('edit','categorias') || roleCan('delete','categorias');
            $colspan = $hasActions ? '5' : '4';
            ?>
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr class="table-light">
                        <th class="d-none d-lg-table-cell col-id">ID</th>
                        <th>Nombre</th>
                        <th class="d-none d-md-table-cell">Tipo</th>
                        <th class="d-none d-xl-table-cell">Descripción</th>
                        <?php if ($hasActions): ?>
                            <th class="text-center">Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody id="tablaCategorias">
                    <?php if (empty($categorias)): ?>
                        <tr><td colspan="<?php echo $colspan; ?>" class="text-center p-4 text-muted">No hay categorías registradas.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categorias as $categoria):
                            $tipoBadge = $categoria['tipo'] === 'Ingreso'
                                ? '<span class="badge bg-success">Ingreso</span>'
                                : '<span class="badge bg-danger">Egreso</span>';
                        ?>
                            <tr>
                                <td class="d-none d-lg-table-cell"><?php echo $categoria['id']; ?></td>
                                <td>
                                    <?php echo htmlspecialchars($categoria['nombre']); ?>
                                    <br class="d-md-none">
                                    <span class="d-md-none"><?php echo $tipoBadge; ?></span>
                                </td>
                                <td class="d-none d-md-table-cell"><?php echo $tipoBadge; ?></td>
                                <td class="d-none d-xl-table-cell"><?php echo htmlspecialchars($categoria['descripcion'] ?: '-'); // Muestra '-' si está vacío ?></td>
                                <?php if ($hasActions): ?>
                                    <td class="text-center align-middle">
                                        <div class="d-flex flex-column flex-sm-row gap-1 justify-content-center align-items-center">
                                            <?php if (roleCan('edit','categorias')): ?>
                                                <button class="btn btn-sm btn-warning btn-edit-categoria"
                                                        data-id="<?php echo $categoria['id']; ?>"
                                                        data-bs-toggle="modal" data-bs-target="#modalCategoria"
                                                        title="Editar Categoría">
                                                     <ion-icon name="create-outline"></ion-icon>
                                                </button>
                                            <?php endif; ?>
                                            <?php if (roleCan('delete','categorias')): ?>
                                                <button class="btn btn-sm btn-danger btn-del-categoria"
                                                        data-id="<?php echo $categoria['id']; ?>"
                                                        title="Eliminar Categoría">
                                                    <ion-icon name="trash-outline"></ion-icon>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>