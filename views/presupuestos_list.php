<?php
// views/presupuestos_list.php
// Llamada por PresupuestoController->index()
// Variables disponibles: $pageTitle, $activeModule, $presupuestos, $currentUser (del layout)
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="text-danger mb-0"><?php echo htmlspecialchars($pageTitle); ?>: Resumen General</h3>
    <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalPresupuesto" id="btnNuevoPresupuesto">
        <ion-icon name="add-circle-outline" class="me-1"></ion-icon> Agregar/Actualizar Presupuesto
    </button>
</div>

<div class="action-menu mb-4 d-none">
    <button class="btn active">Ver Presupuesto</button>
    <button class="btn" data-bs-toggle="modal" data-bs-target="#modalPresupuesto">Agregar</button>
    <button class="btn">Actualizar</button> </div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead>
                    <tr class="table-light">
                        <th>Categoría</th>
                        <th class="text-end">Monto</th>
                        <th>Fecha Asig.</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaPresupuestos">
                    <?php if (empty($presupuestos)): ?>
                        <tr><td colspan="4" class="text-center p-4 text-muted">No hay presupuestos asignados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($presupuestos as $presupuesto):
                            $monto = isset($presupuesto['monto']) ? $presupuesto['monto'] : ($presupuesto['monto_limite'] ?? 0);
                            $montoFormateado = number_format((float)$monto, 2);
                            try {
                                if (class_exists('NumberFormatter')) {
                                    $formatter = new NumberFormatter('es-MX', NumberFormatter::CURRENCY);
                                    if (is_numeric($monto)) {
                                        $montoFormateado = $formatter->formatCurrency($monto, 'MXN');
                                    }
                                }
                            } catch (Exception $e) { /* Ignorar */ }
                            $categoria = htmlspecialchars($presupuesto['categoria'] ?? ($presupuesto['cat_nombre'] ?? '-'));
                            $fechaDisplay = htmlspecialchars($presupuesto['fecha'] ?? '-');
                            $presId = $presupuesto['id'] ?? ($presupuesto['id_presupuesto'] ?? 0);
                        ?>
                            <tr>
                                <td><?php echo $categoria; ?></td>
                                <td class="text-end fw-bold"><?php echo $montoFormateado; ?></td>
                                <td><?php echo $fechaDisplay; ?></td>
                                <td class="text-center">
                                    <div class="btn-responsive-sm">
                                        <button class="btn btn-sm btn-warning btn-edit-presupuesto"
                                                data-id="<?php echo $presId; ?>"
                                                data-bs-toggle="modal" data-bs-target="#modalPresupuesto"
                                                title="Editar Presupuesto">
                                             <ion-icon name="create-outline"></ion-icon> Editar
                                        </button>
                                        <button class="btn btn-sm btn-danger btn-del-presupuesto"
                                                data-id="<?php echo $presId; ?>"
                                                title="Eliminar Presupuesto">
                                            <ion-icon name="trash-outline"></ion-icon> Eliminar
                                        </button>
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